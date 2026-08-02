<?php

namespace App\Modules\Wallet\Services;

use App\Models\CryptoDepositWallet;
use App\Models\CryptoSellRequest;
use App\Models\OtcPricingSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletAllocationService
{
    /**
     * Allocate a deposit wallet and a unique crypto fingerprint for this quote.
     *
     * @return array{
     *   wallet: CryptoDepositWallet,
     *   amount_crypto: float,
     *   amount_crypto_base: float
     * }
     */
    public function allocate(string $coin, string $network, float $baseAmountCrypto): array
    {
        $coin = strtoupper(trim($coin));
        $network = $this->canonicalizeNetwork($coin, $network);
        $precision = $this->precisionFor($coin);
        $base = $this->roundAmount($baseAmountCrypto, $precision);
        if ($base <= 0) {
            throw new RuntimeException('Invalid crypto amount for allocation.');
        }

        $maxPerWallet = $this->maxOrdersPerWallet();
        $maxNudges = max(1, (int) config('crypto.fingerprint_max_nudges', 50));
        $unit = $this->unitFor($precision);

        return DB::transaction(function () use ($coin, $network, $base, $precision, $maxPerWallet, $maxNudges, $unit) {
            $candidates = $this->eligibleWallets($coin, $network, $maxPerWallet, lock: true);
            if ($candidates->isEmpty()) {
                throw new RuntimeException(
                    'No deposit wallet currently available. Please try again in a few minutes.'
                );
            }

            foreach ($candidates as $wallet) {
                $taken = $this->openAmountsOnWallet($wallet, lock: true);
                $candidate = $base;
                $ok = false;
                for ($i = 0; $i < $maxNudges; $i++) {
                    $key = $this->amountKey($candidate, $precision);
                    if (! isset($taken[$key])) {
                        $ok = true;
                        break;
                    }
                    $candidate = $this->addUnit($candidate, $unit, $precision);
                }

                if (! $ok) {
                    continue;
                }

                $wallet->forceFill(['last_allocated_at' => now()])->save();

                return [
                    'wallet' => $wallet->fresh(),
                    'amount_crypto' => $candidate,
                    'amount_crypto_base' => $base,
                ];
            }

            throw new RuntimeException(
                'No deposit wallet currently available. Please try again in a few minutes.'
            );
        });
    }

    public function maxActiveWallets(): int
    {
        return (int) config('crypto.max_active_wallets_per_network', 5);
    }

    public function maxOrdersPerWallet(): int
    {
        $settings = OtcPricingSetting::current();
        $fromDb = (int) ($settings->max_orders_per_wallet ?? 0);

        return $fromDb > 0
            ? $fromDb
            : (int) config('crypto.max_orders_per_wallet', 8);
    }

    public function activeWalletCount(string $coin, string $network): int
    {
        return CryptoDepositWallet::query()
            ->active()
            ->whereRaw('UPPER(coin) = ?', [strtoupper($coin)])
            ->whereRaw('LOWER(network) = ?', [strtolower($network)])
            ->count();
    }

    public function canActivateAnother(string $coin, string $network, ?int $exceptWalletId = null): bool
    {
        $q = CryptoDepositWallet::query()
            ->active()
            ->whereRaw('UPPER(coin) = ?', [strtoupper($coin)])
            ->whereRaw('LOWER(network) = ?', [strtolower($network)])
            ->lockForUpdate();
        if ($exceptWalletId) {
            $q->where('id', '!=', $exceptWalletId);
        }

        return $q->count() < $this->maxActiveWallets();
    }

    /**
     * Open orders that still occupy wallet capacity / fingerprints.
     * Quote expiry only drops waiting_deposit; post-submit orders always count.
     */
    public function applyOccupyingOrdersFilter(Builder $query): Builder
    {
        return $query
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->where(function ($q) {
                $q->whereNotIn('status', [
                    CryptoSellRequest::STATUS_WAITING_DEPOSIT,
                    'pending',
                ])->orWhere(function ($q2) {
                    $q2->whereIn('status', [
                        CryptoSellRequest::STATUS_WAITING_DEPOSIT,
                        'pending',
                    ])->where(function ($q3) {
                        $q3->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    });
                });
            });
    }

    /**
     * Whitelist of canonical network IDs for a coin (from config).
     *
     * @return list<string>
     */
    public function networkIdsForCoin(string $coin): array
    {
        $map = config('crypto.network_ids_by_coin', []);
        $list = $map[strtoupper(trim($coin))] ?? [];

        return array_values(array_filter($list, fn ($id) => is_string($id) && $id !== ''));
    }

    /**
     * Wallet / order network labels for a coin (from catalog allowed IDs, else config).
     *
     * @return list<string>
     */
    public function networksForCoin(string $coin): array
    {
        $coin = strtoupper(trim($coin));
        $ids = [];
        $fromCatalog = false;

        if (\Illuminate\Support\Facades\Schema::hasTable('exchange_rates')
            && \Illuminate\Support\Facades\Schema::hasColumn('exchange_rates', 'allowed_network_ids')) {
            $row = \App\Models\ExchangeRate::query()
                ->whereRaw('UPPER(asset) = ?', [$coin])
                ->first();
            if ($row) {
                $fromCatalog = is_array($row->allowed_network_ids);
                $ids = $row->resolvedNetworkIds();
            }
        }

        // Explicit empty catalog list means no deposit networks — do not revive via config.
        if ($ids === [] && ! $fromCatalog) {
            $ids = $this->networkIdsForCoin($coin);
        }

        if ($ids !== []) {
            return array_values(array_unique(array_map(
                fn (string $id) => $this->walletLabelForNetworkId($coin, $id),
                $ids
            )));
        }

        if ($fromCatalog) {
            return [];
        }

        // Legacy label map fallback.
        $map = config('crypto.networks_by_coin', []);
        $list = $map[$coin] ?? [];

        return array_values(array_filter($list, fn ($n) => is_string($n) && $n !== ''));
    }

    /**
     * Human wallet/order label for a monitored network ID (storage form used today).
     */
    public function walletLabelForNetworkId(string $coin, string $networkId): string
    {
        $coin = strtoupper(trim($coin));
        $networkId = strtolower(trim($networkId));

        return match ($networkId) {
            'bitcoin' => 'Bitcoin',
            'ethereum' => in_array($coin, ['ETH'], true) ? 'Ethereum' : 'ERC20',
            'tron' => 'TRC20',
            'bep20' => 'BEP20',
            'polygon' => 'Polygon',
            'base' => 'Base',
            'arbitrum' => 'Arbitrum',
            'solana' => 'Solana',
            default => (string) (config('crypto.monitored_networks.'.$networkId.'.label') ?? $networkId),
        };
    }

    /**
     * Display label for UI (from monitored_networks).
     */
    public function displayLabelForNetworkId(string $networkId): string
    {
        $networkId = strtolower(trim($networkId));
        $label = config('crypto.monitored_networks.'.$networkId.'.label');

        return is_string($label) && $label !== '' ? $label : $networkId;
    }

    public function canonicalizeNetwork(string $coin, string $network): string
    {
        $allowed = $this->networksForCoin($coin);
        foreach ($allowed as $label) {
            if (strcasecmp($label, $network) === 0) {
                return $label;
            }
        }

        // Accept canonical IDs and map to wallet labels.
        $ids = $this->networkIdsForCoin($coin);
        $catalog = app(\App\Modules\Wallet\Services\Blockchain\MonitoredNetworkCatalog::class);
        try {
            $resolvedId = $catalog->resolveId($network);
        } catch (\Throwable) {
            $resolvedId = strtolower(trim($network));
        }
        if (in_array($resolvedId, $ids, true) || ($ids === [] && $resolvedId !== '')) {
            $label = $this->walletLabelForNetworkId($coin, $resolvedId);
            if ($allowed === [] || in_array($label, $allowed, true)) {
                return $label;
            }
            // If label differs but ID is whitelisted, still allow.
            if (in_array($resolvedId, $ids, true)) {
                return $label;
            }
        }

        $aliases = [
            'btc' => 'Bitcoin',
            'bitcoin' => 'Bitcoin',
            'eth' => 'Ethereum',
            'ethereum' => 'Ethereum',
            'trc20' => 'TRC20',
            'tron' => 'TRC20',
            'erc20' => 'ERC20',
            'bep20' => 'BEP20',
            'bsc' => 'BEP20',
            'polygon' => 'Polygon',
            'matic' => 'Polygon',
            'sol' => 'Solana',
            'solana' => 'Solana',
            'base' => 'Base',
            'arbitrum' => 'Arbitrum',
            'arb' => 'Arbitrum',
        ];
        $key = strtolower(trim($network));
        if (isset($aliases[$key]) && in_array($aliases[$key], $allowed, true)) {
            return $aliases[$key];
        }

        throw new RuntimeException("Unsupported network {$network} for {$coin}.");
    }

    public function precisionFor(string $coin): int
    {
        $map = config('crypto.amount_precision', []);

        return (int) ($map[strtoupper($coin)] ?? 8);
    }

    public function unitFor(int $precision): float
    {
        return (float) bcpow('10', (string) (-$precision), $precision);
    }

    public function roundAmount(float $amount, int $precision): float
    {
        return (float) bcadd(sprintf('%.20F', $amount), '0', $precision);
    }

    public function addUnit(float $amount, float $unit, int $precision): float
    {
        return (float) bcadd(
            $this->amountKey($amount, $precision),
            $this->amountKey($unit, $precision),
            $precision
        );
    }

    public function amountKey(float $amount, int $precision): string
    {
        return bcadd(sprintf('%.20F', $amount), '0', $precision);
    }

    public function tokenContract(string $coin, string $network): ?string
    {
        $map = config('crypto.token_contracts.'.strtoupper($coin), []);
        if (! is_array($map)) {
            return null;
        }
        try {
            $canonical = $this->canonicalizeNetwork($coin, $network);
        } catch (\Throwable) {
            $canonical = $network;
        }

        $contract = $map[$canonical] ?? null;

        return is_string($contract) && $contract !== '' ? $contract : null;
    }

    /**
     * @return Collection<int, CryptoDepositWallet>
     */
    private function eligibleWallets(string $coin, string $network, int $maxPerWallet, bool $lock = false): Collection
    {
        $q = CryptoDepositWallet::query()
            ->active()
            ->whereRaw('UPPER(coin) = ?', [$coin])
            ->whereRaw('LOWER(network) = ?', [strtolower($network)])
            ->orderBy('id');

        if ($lock) {
            $q->lockForUpdate();
        }

        return $q->get()
            ->filter(fn (CryptoDepositWallet $w) => $this->occupyingCount($w) < $maxPerWallet)
            ->sortBy([
                fn (CryptoDepositWallet $w) => $this->occupyingCount($w),
                fn (CryptoDepositWallet $w) => $w->last_allocated_at?->timestamp ?? 0,
                fn (CryptoDepositWallet $w) => $w->id,
            ])
            ->values();
    }

    private function occupyingCount(CryptoDepositWallet $wallet): int
    {
        $q = CryptoSellRequest::query()
            ->where('platform_address', $wallet->address)
            ->whereRaw('UPPER(coin) = ?', [strtoupper($wallet->coin)])
            ->whereRaw('LOWER(network) = ?', [strtolower((string) $wallet->network)]);

        return $this->applyOccupyingOrdersFilter($q)->count();
    }

    /**
     * @return array<string, true>
     */
    private function openAmountsOnWallet(CryptoDepositWallet $wallet, bool $lock = false): array
    {
        $precision = $this->precisionFor($wallet->coin);
        $q = CryptoSellRequest::query()
            ->where('platform_address', $wallet->address)
            ->whereRaw('UPPER(coin) = ?', [strtoupper($wallet->coin)])
            ->whereRaw('LOWER(network) = ?', [strtolower($wallet->network)]);

        $this->applyOccupyingOrdersFilter($q);

        if ($lock) {
            $q->lockForUpdate();
        }

        $rows = $q->pluck('amount_crypto');

        $taken = [];
        foreach ($rows as $amt) {
            $taken[$this->amountKey((float) $amt, $precision)] = true;
        }

        return $taken;
    }
}
