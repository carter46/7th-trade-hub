<?php

namespace App\Modules\Wallet\Services\Blockchain;

use App\Models\CryptoDepositWallet;
use App\Models\CryptoSellRequest;
use App\Models\IncomingCryptoTransaction;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Log;

class DepositMonitorService
{
    public function __construct(
        private ExplorerHttp $http,
        private ExplorerClientRegistry $registry,
        private MonitoredNetworkCatalog $catalog,
        private DepositMatchingService $matcher,
        private NotificationDispatcher $notifications,
    ) {}

    /**
     * Poll all relevant wallets and update confirmations.
     *
     * @return array{wallets: int, detected: int, errors: list<string>}
     */
    public function poll(): array
    {
        $provider = $this->http->monitoringProvider();
        if (! $provider->enabled) {
            return ['wallets' => 0, 'detected' => 0, 'errors' => ['Monitoring disabled']];
        }

        $wallets = CryptoDepositWallet::query()
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhereIn('id', CryptoSellRequest::query()
                        ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
                        ->whereNotNull('crypto_deposit_wallet_id')
                        ->select('crypto_deposit_wallet_id'));
            })
            ->get();

        $addressKeys = $wallets->map(
            fn ($w) => strtoupper($w->coin).'|'.$this->catalog->resolveId((string) $w->network).'|'.$w->address
        )->all();
        $orphanAddresses = CryptoSellRequest::query()
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->whereNotNull('platform_address')
            ->get(['coin', 'network', 'platform_address'])
            ->unique(fn ($r) => strtoupper($r->coin).'|'.$this->catalog->resolveId((string) $r->network).'|'.$r->platform_address);

        $detected = 0;
        $errors = [];
        $health = $provider->meta['network_health'] ?? [];
        $walletCounts = $this->walletCountsByNetwork();

        // Group by network_id|address to avoid duplicate explorer calls when coins share an address.
        $groups = [];
        foreach ($wallets as $wallet) {
            $networkId = $this->catalog->resolveId($wallet->network);
            $groupKey = $networkId.'|'.$wallet->address;
            $groups[$groupKey] ??= [
                'network_id' => $networkId,
                'address' => $wallet->address,
                'network_label' => $this->catalog->label($networkId),
                'wallets' => [],
            ];
            $groups[$groupKey]['wallets'][] = $wallet;
        }

        foreach ($groups as $group) {
            $networkId = $group['network_id'];
            $seenHashes = [];
            foreach ($group['wallets'] as $wallet) {
                try {
                    $started = microtime(true);
                    $resolved = $this->registry->resolve($wallet->network);
                    $client = $resolved['client'];
                    $transfers = $client->fetchIncoming($wallet->address, $wallet->coin, $wallet->network);
                    $latencyMs = (int) round((microtime(true) - $started) * 1000);

                    $tip = null;
                    try {
                        $tip = $client->tipHeight($wallet->network);
                    } catch (\Throwable) {
                        $tip = null;
                    }

                    $health[$networkId] = $this->healthyMeta($resolved, $walletCounts[$networkId] ?? null, $latencyMs, $tip);

                    foreach ($transfers as $transfer) {
                        $hash = (string) ($transfer['tx_hash'] ?? '');
                        if ($hash !== '' && isset($seenHashes[$hash])) {
                            continue;
                        }
                        if ($hash !== '') {
                            $seenHashes[$hash] = true;
                        }
                        if ($this->persistTransfer($transfer, $wallet->network)) {
                            $detected++;
                        }
                    }
                    if ($wallet->is_active && $transfers !== []) {
                        $wallet->forceFill(['last_deposit_at' => now()])->save();
                    }
                } catch (\Throwable $e) {
                    $resolved = null;
                    try {
                        $resolved = $this->registry->resolve($wallet->network);
                    } catch (\Throwable) {
                        // ignore
                    }
                    $health[$networkId] = $this->errorMeta(
                        $e->getMessage(),
                        $resolved,
                        $walletCounts[$networkId] ?? null
                    );
                    $errors[] = "{$wallet->coin}/{$wallet->network}: ".$e->getMessage();
                    $this->notifyExplorerOffline($networkId, $e->getMessage());
                    Log::channel('financial')->warning('Deposit poll failed', [
                        'wallet_id' => $wallet->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        foreach ($orphanAddresses as $row) {
            $key = strtoupper($row->coin).'|'.strtolower((string) $row->network).'|'.$row->platform_address;
            if (in_array($key, $addressKeys, true)) {
                continue;
            }
            $networkId = $this->catalog->resolveId((string) $row->network);
            try {
                $started = microtime(true);
                $resolved = $this->registry->resolve((string) $row->network);
                $client = $resolved['client'];
                $transfers = $client->fetchIncoming($row->platform_address, $row->coin, (string) $row->network);
                $latencyMs = (int) round((microtime(true) - $started) * 1000);
                $tip = null;
                try {
                    $tip = $client->tipHeight((string) $row->network);
                } catch (\Throwable) {
                    $tip = null;
                }
                $health[$networkId] = $this->healthyMeta($resolved, $walletCounts[$networkId] ?? null, $latencyMs, $tip);
                foreach ($transfers as $transfer) {
                    if ($this->persistTransfer($transfer, (string) $row->network)) {
                        $detected++;
                    }
                }
            } catch (\Throwable $e) {
                $resolved = null;
                try {
                    $resolved = $this->registry->resolve((string) $row->network);
                } catch (\Throwable) {
                    // ignore
                }
                $health[$networkId] = $this->errorMeta($e->getMessage(), $resolved, $walletCounts[$networkId] ?? null);
                $errors[] = "orphan {$row->coin}: ".$e->getMessage();
            }
        }

        // Seed idle rows for configured networks with wallet counts even if not polled.
        foreach ($this->catalog->ids() as $networkId) {
            if (isset($health[$networkId])) {
                $counts = $walletCounts[$networkId] ?? ['active' => 0, 'disabled' => 0];
                $health[$networkId]['wallets_active'] = $counts['active'];
                $health[$networkId]['wallets_disabled'] = $counts['disabled'];
                continue;
            }
            $counts = $walletCounts[$networkId] ?? ['active' => 0, 'disabled' => 0];
            try {
                $resolved = $this->registry->resolve($networkId);
            } catch (\Throwable) {
                $resolved = null;
            }
            $health[$networkId] = [
                'status' => ($counts['active'] + $counts['disabled']) > 0 ? 'idle' : 'not_configured',
                'provider' => $resolved['provider'] ?? 'native',
                'client' => $resolved['client_key'] ?? null,
                'endpoint' => $resolved['endpoint'] ?? null,
                'auth_status' => $resolved['auth_status'] ?? 'unknown',
                'last_poll_at' => null,
                'last_success_at' => null,
                'last_error' => null,
                'last_error_at' => null,
                'latency_ms' => null,
                'tip_height' => null,
                'wallets_active' => $counts['active'],
                'wallets_disabled' => $counts['disabled'],
            ];
        }

        $this->refreshConfirmations();
        $this->matcher->matchUnmatched();

        $meta = $provider->meta ?? [];
        $meta['network_health'] = $health;
        $meta['last_poll_at'] = now()->toIso8601String();
        $provider->meta = $meta;
        $provider->last_sync_at = now();
        if ($errors === []) {
            $provider->status = 'connected';
            $provider->last_success_at = now();
            $provider->last_error = null;
        } else {
            $provider->status = 'error';
            $provider->last_error = mb_substr(implode('; ', $errors), 0, 2000);
            $provider->last_error_at = now();
        }
        $provider->save();

        return ['wallets' => $wallets->count(), 'detected' => $detected, 'errors' => $errors];
    }

    /**
     * @param  array{
     *   tx_hash: string,
     *   amount: float,
     *   block_height: ?int,
     *   confirmations: int,
     *   from_address: ?string,
     *   to_address: string,
     *   coin: string,
     *   network: string,
     *   token_contract?: ?string,
     *   raw: array<string, mixed>
     * }  $transfer
     */
    public function persistTransfer(array $transfer, string $fallbackNetwork): bool
    {
        $hash = $transfer['tx_hash'];
        $existing = IncomingCryptoTransaction::query()->where('tx_hash', $hash)->first();
        if ($existing) {
            $updates = [
                'confirmations' => max((int) $existing->confirmations, (int) $transfer['confirmations']),
            ];
            if ($existing->block_height === null && $transfer['block_height']) {
                $updates['block_height'] = $transfer['block_height'];
            }
            if ($existing->token_contract === null && ! empty($transfer['token_contract'])) {
                $updates['token_contract'] = $transfer['token_contract'];
            }
            $existing->fill($updates)->save();

            return false;
        }

        $networkRaw = $transfer['network'] ?: $fallbackNetwork;
        try {
            $networkId = app(\App\Modules\Wallet\Services\NetworkRegistry::class)->resolveId((string) $networkRaw);
        } catch (\Throwable) {
            $networkId = strtolower(trim((string) $networkRaw));
        }

        $row = IncomingCryptoTransaction::query()->create([
            'coin' => strtoupper($transfer['coin']),
            'network' => $networkId,
            'wallet_address' => $transfer['to_address'],
            'tx_hash' => $hash,
            'amount' => $transfer['amount'],
            'block_height' => $transfer['block_height'],
            'confirmations' => $transfer['confirmations'],
            'from_address' => $transfer['from_address'],
            'token_contract' => $transfer['token_contract'] ?? null,
            'detected_at' => now(),
            'status' => IncomingCryptoTransaction::STATUS_DETECTED,
            'raw' => $transfer['raw'],
        ]);

        $this->notifications->notifyAdmins(new NotificationMessage(
            type: 'crypto.deposit_detected',
            title: 'Deposit Detected',
            body: sprintf(
                '%s (%s) %.8f — tx %s',
                $row->coin,
                $this->catalog->label((string) $row->network),
                (float) $row->amount,
                $row->tx_hash
            ),
            actionUrl: route('admin.incoming-deposits'),
            meta: ['severity' => 'info', 'tx_hash' => $row->tx_hash],
            priority: 'normal',
            permission: 'finance.manage',
            dedupeKey: 'detect:'.$row->tx_hash,
        ), ['database']);

        return true;
    }

    public function refreshConfirmations(): void
    {
        $rows = IncomingCryptoTransaction::query()
            ->whereIn('status', [
                IncomingCryptoTransaction::STATUS_DETECTED,
                IncomingCryptoTransaction::STATUS_MATCHED,
                IncomingCryptoTransaction::STATUS_READY,
            ])
            ->whereNotNull('block_height')
            ->get();

        foreach ($rows as $row) {
            try {
                $client = $this->clientForNetwork($row->network);
                $tip = $client->tipHeight($row->network);
                if (! $tip || ! $row->block_height) {
                    continue;
                }
                $conf = max(0, $tip - (int) $row->block_height + 1);
                if ($conf === (int) $row->confirmations) {
                    continue;
                }
                $row->confirmations = $conf;
                $row->save();

                if ($row->matched_order_id) {
                    $order = CryptoSellRequest::find($row->matched_order_id);
                    if ($order) {
                        $order->confirmations_observed = $conf;
                        $required = (int) ($order->required_confirmations ?? 1);
                        if ($conf >= $required && $row->status !== IncomingCryptoTransaction::STATUS_READY) {
                            $row->status = IncomingCryptoTransaction::STATUS_READY;
                            $row->save();
                            if ($order->status === CryptoSellRequest::STATUS_SUBMITTED
                                || $order->status === CryptoSellRequest::STATUS_VERIFYING
                                || $order->status === CryptoSellRequest::STATUS_WAITING_DEPOSIT) {
                                $order->status = CryptoSellRequest::STATUS_VERIFYING;
                            }
                            $order->save();

                            $this->notifications->notifyAdmins(new NotificationMessage(
                                type: 'crypto.deposit_ready',
                                title: 'Deposit Ready',
                                body: sprintf('Order #%d — %s confirmations met', $order->id, $order->coin),
                                actionUrl: route('admin.crypto-sells.show', $order),
                                meta: ['severity' => 'ready', 'order_id' => $order->id],
                                priority: 'high',
                                permission: 'finance.manage',
                                dedupeKey: 'ready:'.$row->tx_hash,
                            ), ['database']);
                        } else {
                            $order->save();
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::channel('financial')->debug('Confirmation refresh failed', [
                    'tx' => $row->tx_hash,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function clientForNetwork(string $network): ChainExplorerClient
    {
        return $this->registry->clientForNetwork($network);
    }

    public function normalizeNetwork(string $network): string
    {
        return $this->catalog->resolveId($network);
    }

    /**
     * @return array<string, array{active: int, disabled: int}>
     */
    private function walletCountsByNetwork(): array
    {
        $counts = [];
        foreach ($this->catalog->ids() as $id) {
            $counts[$id] = ['active' => 0, 'disabled' => 0];
        }

        CryptoDepositWallet::query()
            ->select(['network', 'is_active'])
            ->get()
            ->each(function (CryptoDepositWallet $wallet) use (&$counts) {
                $id = $this->catalog->resolveId((string) $wallet->network);
                if (! isset($counts[$id])) {
                    $counts[$id] = ['active' => 0, 'disabled' => 0];
                }
                if ($wallet->is_active) {
                    $counts[$id]['active']++;
                } else {
                    $counts[$id]['disabled']++;
                }
            });

        return $counts;
    }

    /**
     * @param  array{client: ChainExplorerClient, provider: string, client_key: string, network_id: string, endpoint: string, auth_status: string}  $resolved
     * @param  array{active: int, disabled: int}|null  $counts
     * @return array<string, mixed>
     */
    private function healthyMeta(array $resolved, ?array $counts, int $latencyMs, ?int $tip): array
    {
        return [
            'status' => 'healthy',
            'provider' => $resolved['provider'],
            'client' => $resolved['client_key'],
            'endpoint' => $resolved['endpoint'],
            'auth_status' => $resolved['auth_status'],
            'last_poll_at' => now()->toIso8601String(),
            'last_success_at' => now()->toIso8601String(),
            'last_error' => null,
            'last_error_at' => null,
            'latency_ms' => $latencyMs,
            'tip_height' => $tip,
            'wallets_active' => $counts['active'] ?? 0,
            'wallets_disabled' => $counts['disabled'] ?? 0,
            'consecutive_failures' => 0,
        ];
    }

    /**
     * @param  array{client: ChainExplorerClient, provider: string, client_key: string, network_id: string, endpoint: string, auth_status: string}|null  $resolved
     * @param  array{active: int, disabled: int}|null  $counts
     * @return array<string, mixed>
     */
    private function errorMeta(string $error, ?array $resolved, ?array $counts): array
    {
        $auth = $resolved['auth_status'] ?? 'unknown';
        $lower = strtolower($error);
        if (str_contains($lower, 'missing') && str_contains($lower, 'key')) {
            $auth = 'missing_key';
        } elseif (str_contains($lower, 'auth')) {
            $auth = 'unauthorized';
        }

        return [
            'status' => 'error',
            'provider' => $resolved['provider'] ?? 'native',
            'client' => $resolved['client_key'] ?? null,
            'endpoint' => $resolved['endpoint'] ?? null,
            'auth_status' => $auth,
            'last_poll_at' => now()->toIso8601String(),
            'last_success_at' => null,
            'last_error_at' => now()->toIso8601String(),
            'last_error' => mb_substr($error, 0, 500),
            'latency_ms' => null,
            'tip_height' => null,
            'wallets_active' => $counts['active'] ?? 0,
            'wallets_disabled' => $counts['disabled'] ?? 0,
            'consecutive_failures' => 1,
        ];
    }

    private function notifyExplorerOffline(string $network, string $error): void
    {
        $this->notifications->notifyAdmins(new NotificationMessage(
            type: 'crypto.explorer_offline',
            title: 'Explorer Offline',
            body: $this->catalog->label($network).': '.$error,
            actionUrl: route('admin.blockchain-monitoring'),
            meta: ['severity' => 'critical', 'network' => $network],
            priority: 'high',
            permission: 'finance.manage',
            dedupeKey: 'offline:'.$network.':'.now()->format('Y-m-d-H'),
        ), ['database']);
    }
}
