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
    public function __construct(
        private NetworkRegistry $networks,
    ) {}

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
        return $this->walletsOnNetworkQuery($coin, $network)->active()->count();
    }

    public function canActivateAnother(string $coin, string $network, ?int $exceptWalletId = null): bool
    {
        $q = $this->walletsOnNetworkQuery($coin, $network)->active()->lockForUpdate();
        if ($exceptWalletId) {
            $q->where('id', '!=', $exceptWalletId);
        }

        return $q->count() < $this->maxActiveWallets();
    }

    /**
     * @return Builder<\App\Models\CryptoDepositWallet>
     */
    private function walletsOnNetworkQuery(string $coin, string $network): Builder
    {
        $variants = $this->networks->storageVariants($network);
        $placeholders = implode(',', array_fill(0, count($variants), '?'));

        return CryptoDepositWallet::query()
            ->whereRaw('UPPER(coin) = ?', [strtoupper($coin)])
            ->whereRaw('LOWER(network) IN ('.$placeholders.')', $variants);
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
     * Soft-suggested network IDs for a symbol (not a runtime ceiling).
     *
     * @return list<string>
     */
    public function networkIdsForCoin(string $coin): array
    {
        return $this->networks->suggestDefaultsForAsset($coin);
    }

    /**
     * Canonical network IDs allowed for a coin (Coin Catalog SoT).
     *
     * @return list<string>
     */
    public function networksForCoin(string $coin): array
    {
        return $this->networks->monitorableIdsForCoin($coin);
    }

    /**
     * @deprecated Use NetworkRegistry::label(). Kept for callers during transition.
     */
    public function walletLabelForNetworkId(string $coin, string $networkId): string
    {
        return $this->displayLabelForNetworkId($networkId);
    }

    /**
     * Display label for UI (admins never see raw IDs).
     */
    public function displayLabelForNetworkId(string $networkId): string
    {
        return $this->networks->label($networkId);
    }

    /**
     * Resolve input to a canonical network ID allowed for the coin.
     */
    public function canonicalizeNetwork(string $coin, string $network): string
    {
        $resolvedId = $this->networks->resolveId($network);
        $allowed = $this->networks->monitorableIdsForCoin($coin);

        if (in_array($resolvedId, $allowed, true)) {
            return $resolvedId;
        }

        $catalogIds = $this->networks->idsForCoin($coin);
        if ($catalogIds === []) {
            throw new RuntimeException(
                "{$coin} has no deposit networks in Coin Catalog. Assign a monitorable network before taking OTC deposits."
            );
        }
        if (in_array($resolvedId, $catalogIds, true) && ! $this->networks->isMonitorable($resolvedId)) {
            throw new RuntimeException(
                "Network {$this->networks->label($resolvedId)} has no blockchain monitor configured."
            );
        }

        throw new RuntimeException("Unsupported network for {$coin}.");
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
        return $this->networks->tokenContract($coin, $network);
    }

    /**
     * @return Collection<int, CryptoDepositWallet>
     */
    private function eligibleWallets(string $coin, string $network, int $maxPerWallet, bool $lock = false): Collection
    {
        $q = $this->walletsOnNetworkQuery($coin, $network)->active()->orderBy('id');

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
        return $this->applyOccupyingOrdersFilter($this->ordersOnWalletQuery($wallet))->count();
    }

    /**
     * @return array<string, true>
     */
    private function openAmountsOnWallet(CryptoDepositWallet $wallet, bool $lock = false): array
    {
        $precision = $this->precisionFor($wallet->coin);
        $q = $this->ordersOnWalletQuery($wallet);
        $this->applyOccupyingOrdersFilter($q);

        if ($lock) {
            $q->lockForUpdate();
        }

        $taken = [];
        foreach ($q->pluck('amount_crypto') as $amt) {
            $taken[$this->amountKey((float) $amt, $precision)] = true;
        }

        return $taken;
    }

    /**
     * @return Builder<\App\Models\CryptoSellRequest>
     */
    private function ordersOnWalletQuery(CryptoDepositWallet $wallet): Builder
    {
        $variants = $this->networks->storageVariants((string) $wallet->network);
        $placeholders = implode(',', array_fill(0, count($variants), '?'));

        return CryptoSellRequest::query()
            ->where('platform_address', $wallet->address)
            ->whereRaw('UPPER(coin) = ?', [strtoupper($wallet->coin)])
            ->whereRaw('LOWER(network) IN ('.$placeholders.')', $variants);
    }
}
