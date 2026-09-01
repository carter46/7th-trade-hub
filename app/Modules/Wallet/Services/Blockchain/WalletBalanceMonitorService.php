<?php

namespace App\Modules\Wallet\Services\Blockchain;

use App\Models\CryptoDepositWallet;
use App\Models\IncomingCryptoTransaction;
use App\Models\WalletBalanceHistory;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Log;

class WalletBalanceMonitorService
{
    public function __construct(
        private ExplorerHttp $http,
        private ExplorerClientRegistry $registry,
        private MonitoredNetworkCatalog $catalog,
        private NotificationDispatcher $notifications,
    ) {}

    /**
     * Poll current balances for all deposit wallets, grouped by network.
     *
     * @return array{wallets: int, updated: int, errors: list<string>}
     */
    public function poll(): array
    {
        $provider = $this->http->monitoringProvider();
        if (! $provider->enabled) {
            return ['wallets' => 0, 'updated' => 0, 'errors' => ['Monitoring disabled']];
        }

        $wallets = CryptoDepositWallet::query()->orderBy('id')->get();
        if ($wallets->isEmpty()) {
            return ['wallets' => 0, 'updated' => 0, 'errors' => []];
        }

        $groups = [];
        foreach ($wallets as $wallet) {
            $networkId = $this->catalog->resolveId($wallet->network);
            $groups[$networkId] ??= [];
            $groups[$networkId][] = $wallet;
        }

        $updated = 0;
        $errors = [];
        $spacingUs = app()->environment('testing') ? 0 : 150_000;

        foreach ($groups as $networkWallets) {
            foreach ($networkWallets as $wallet) {
                try {
                    if ($this->pollWallet($wallet)) {
                        $updated++;
                    }
                } catch (\Throwable $e) {
                    $msg = sprintf(
                        'Wallet #%d %s/%s: %s',
                        $wallet->id,
                        $wallet->coin,
                        $wallet->network,
                        $e->getMessage()
                    );
                    $errors[] = $msg;
                    Log::warning('wallet_balance.poll_failed', [
                        'wallet_id' => $wallet->id,
                        'error' => $e->getMessage(),
                    ]);
                    $wallet->live_balance_error = mb_substr($e->getMessage(), 0, 500);
                    $wallet->save();
                }

                usleep($spacingUs);
            }
        }

        return [
            'wallets' => $wallets->count(),
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * @return bool True when balance was successfully fetched.
     */
    public function pollWallet(CryptoDepositWallet $wallet): bool
    {
        $previous = $wallet->live_balance !== null ? (float) $wallet->live_balance : null;
        $previousUpdatedAt = $wallet->live_balance_updated_at;

        $client = $this->registry->clientForNetwork($wallet->network);
        $balance = $client->fetchBalance($wallet->address, $wallet->coin, $wallet->network);

        $now = now();
        $wallet->live_balance = $balance;
        $wallet->live_balance_updated_at = $now;
        $wallet->live_balance_error = null;
        $wallet->save();

        if ($previous === null || abs($balance - $previous) > $this->historyEpsilon($wallet->coin)) {
            WalletBalanceHistory::query()->create([
                'crypto_deposit_wallet_id' => $wallet->id,
                'balance' => $balance,
                'recorded_at' => $now,
            ]);
        }

        if ($previous !== null) {
            $this->evaluateDelta($wallet, $previous, $balance, $previousUpdatedAt);
        }

        return true;
    }

    private function evaluateDelta(
        CryptoDepositWallet $wallet,
        float $previous,
        float $balance,
        mixed $since,
    ): void {
        $delta = $balance - $previous;
        $dust = $this->dustThreshold($wallet->coin);
        if (abs($delta) <= $dust) {
            return;
        }

        if ($delta > 0) {
            $detected = $this->depositAmountSince($wallet, $since);
            if ($delta - $detected > $dust) {
                $this->notifications->notifyAdmins(new NotificationMessage(
                    type: 'treasury.unexpected_increase',
                    title: 'Unexpected treasury increase',
                    body: sprintf(
                        '%s (%s) wallet #%d rose by %.8f (prev %.8f → %.8f). Detected deposits since last sync: %.8f.',
                        $wallet->coin,
                        app(\App\Modules\Wallet\Services\NetworkRegistry::class)->label((string) $wallet->network),
                        $wallet->id,
                        $delta,
                        $previous,
                        $balance,
                        $detected
                    ),
                    actionUrl: route('admin.crypto-wallets.treasury'),
                    meta: [
                        'severity' => 'warning',
                        'wallet_id' => $wallet->id,
                        'delta' => $delta,
                    ],
                    priority: 'high',
                    permission: 'finance.manage',
                    dedupeKey: 'treasury-inc:'.$wallet->id.':'.round($balance, 8),
                ), \App\Services\Notifications\AdminNotificationChannels::FINANCE);
            }

            return;
        }

        // Decrease
        if ($wallet->is_exchange_managed) {
            return;
        }

        $this->notifications->notifyAdmins(new NotificationMessage(
            type: 'treasury.unexpected_decrease',
            title: 'Unexpected treasury decrease',
            body: sprintf(
                '%s (%s) wallet #%d fell by %.8f (prev %.8f → %.8f).',
                $wallet->coin,
                app(\App\Modules\Wallet\Services\NetworkRegistry::class)->label((string) $wallet->network),
                $wallet->id,
                abs($delta),
                $previous,
                $balance
            ),
            actionUrl: route('admin.crypto-wallets.treasury'),
            meta: [
                'severity' => 'critical',
                'wallet_id' => $wallet->id,
                'delta' => $delta,
            ],
            priority: 'high',
            permission: 'finance.manage',
            dedupeKey: 'treasury-dec:'.$wallet->id.':'.round($balance, 8),
        ), \App\Services\Notifications\AdminNotificationChannels::FINANCE);
    }

    private function depositAmountSince(CryptoDepositWallet $wallet, mixed $since): float
    {
        $q = IncomingCryptoTransaction::query()
            ->where('wallet_address', $wallet->address)
            ->whereRaw('UPPER(coin) = ?', [strtoupper($wallet->coin)]);

        if ($since) {
            $q->where('detected_at', '>=', $since);
        }

        return (float) $q->sum('amount');
    }

    private function dustThreshold(string $coin): float
    {
        $coin = strtoupper($coin);
        $configured = config('crypto.balance_dust.'.$coin);
        if (is_numeric($configured) && (float) $configured > 0) {
            return (float) $configured;
        }

        $precision = (int) (config('crypto.amount_precision.'.$coin) ?? 8);

        return 10 ** (-max(0, min($precision, 10)));
    }

    private function historyEpsilon(string $coin): float
    {
        return $this->dustThreshold($coin) / 10;
    }
}
