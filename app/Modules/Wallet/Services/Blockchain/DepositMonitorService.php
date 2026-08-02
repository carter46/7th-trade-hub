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
        private MempoolBitcoinClient $bitcoin,
        private EtherscanClient $etherscan,
        private TronGridClient $tron,
        private SolanaRpcClient $solana,
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

        // Also include addresses still on open orders even if wallet row disabled.
        $addressKeys = $wallets->map(fn ($w) => strtoupper($w->coin).'|'.strtolower($w->network).'|'.$w->address)->all();
        $orphanAddresses = CryptoSellRequest::query()
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->whereNotNull('platform_address')
            ->get(['coin', 'network', 'platform_address'])
            ->unique(fn ($r) => strtoupper($r->coin).'|'.strtolower((string) $r->network).'|'.$r->platform_address);

        $detected = 0;
        $errors = [];
        $health = $provider->meta['network_health'] ?? [];

        foreach ($wallets as $wallet) {
            try {
                $client = $this->clientForNetwork($wallet->network);
                $transfers = $client->fetchIncoming($wallet->address, $wallet->coin, $wallet->network);
                $health[$client->networkKey()] = $this->healthyMeta();
                foreach ($transfers as $transfer) {
                    if ($this->persistTransfer($transfer, $wallet->network)) {
                        $detected++;
                    }
                }
                if ($wallet->is_active && $transfers !== []) {
                    $wallet->forceFill(['last_deposit_at' => now()])->save();
                }
            } catch (\Throwable $e) {
                $key = $this->normalizeNetwork($wallet->network);
                $health[$key] = $this->errorMeta($e->getMessage());
                $errors[] = "{$wallet->coin}/{$wallet->network}: ".$e->getMessage();
                $this->notifyExplorerOffline($key, $e->getMessage());
                Log::channel('financial')->warning('Deposit poll failed', [
                    'wallet_id' => $wallet->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        foreach ($orphanAddresses as $row) {
            $key = strtoupper($row->coin).'|'.strtolower((string) $row->network).'|'.$row->platform_address;
            if (in_array($key, $addressKeys, true)) {
                continue;
            }
            try {
                $client = $this->clientForNetwork((string) $row->network);
                $transfers = $client->fetchIncoming($row->platform_address, $row->coin, (string) $row->network);
                $health[$client->networkKey()] = $this->healthyMeta();
                foreach ($transfers as $transfer) {
                    if ($this->persistTransfer($transfer, (string) $row->network)) {
                        $detected++;
                    }
                }
            } catch (\Throwable $e) {
                $n = $this->normalizeNetwork((string) $row->network);
                $health[$n] = $this->errorMeta($e->getMessage());
                $errors[] = "orphan {$row->coin}: ".$e->getMessage();
            }
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

        $row = IncomingCryptoTransaction::query()->create([
            'coin' => strtoupper($transfer['coin']),
            'network' => $transfer['network'] ?: $fallbackNetwork,
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
                $row->network,
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
        $n = $this->normalizeNetwork($network);

        return match ($n) {
            'bitcoin', 'btc' => $this->bitcoin,
            'ethereum', 'eth', 'erc20', 'bep20', 'polygon', 'base', 'arbitrum' => $this->etherscan,
            'tron', 'trc20' => $this->tron,
            'solana', 'sol' => $this->solana,
            default => throw new \InvalidArgumentException("Unsupported network: {$network}"),
        };
    }

    public function normalizeNetwork(string $network): string
    {
        $map = config('crypto.network_client', []);
        foreach ($map as $label => $clientKey) {
            if (strcasecmp((string) $label, $network) === 0) {
                return strtolower((string) $clientKey);
            }
        }

        $key = strtolower(trim($network));
        $aliases = [
            'btc' => 'bitcoin',
            'eth' => 'ethereum',
            'erc20' => 'ethereum',
            'bep20' => 'ethereum',
            'polygon' => 'ethereum',
            'matic' => 'ethereum',
            'base' => 'ethereum',
            'arbitrum' => 'ethereum',
            'arb' => 'ethereum',
            'trc20' => 'tron',
            'sol' => 'solana',
        ];

        return $aliases[$key] ?? $key;
    }

    /** @return array<string, mixed> */
    private function healthyMeta(): array
    {
        return [
            'status' => 'healthy',
            'last_success_at' => now()->toIso8601String(),
            'last_error' => null,
            'consecutive_failures' => 0,
        ];
    }

    /** @return array<string, mixed> */
    private function errorMeta(string $error): array
    {
        return [
            'status' => 'error',
            'last_error_at' => now()->toIso8601String(),
            'last_error' => mb_substr($error, 0, 500),
            'consecutive_failures' => 1,
        ];
    }

    private function notifyExplorerOffline(string $network, string $error): void
    {
        $this->notifications->notifyAdmins(new NotificationMessage(
            type: 'crypto.explorer_offline',
            title: 'Explorer Offline',
            body: strtoupper($network).': '.$error,
            actionUrl: route('admin.settings').'#blockchain-monitoring',
            meta: ['severity' => 'critical', 'network' => $network],
            priority: 'high',
            permission: 'finance.manage',
            dedupeKey: 'offline:'.$network.':'.now()->format('Y-m-d-H'),
        ), ['database']);
    }
}
