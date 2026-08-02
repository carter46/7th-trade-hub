<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    /** Buy rates above this are treated as corrupt full-coin NGN prices, not ₦/$1. */
    public static function maxBuyRatePerUsd(): float
    {
        return (float) config('crypto.max_buy_rate_ngn_per_usd', 10000);
    }

    public static function defaultSpreadNgn(): float
    {
        return (float) config('crypto.default_coin_spread_ngn', 25);
    }

    protected $fillable = [
        'asset',
        'coingecko_id',
        'bybit_symbol',
        'allowed_network_ids',
        'logo_url',
        'buy_rate_ngn',
        'sell_rate_ngn',
        'spread_ngn',
        'minimum_amount',
        'maximum_amount',
        'min_amount_usd',
        'max_amount_usd',
        'processing_time',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'allowed_network_ids' => 'array',
            'buy_rate_ngn' => 'decimal:2',
            'sell_rate_ngn' => 'decimal:2',
            'spread_ngn' => 'decimal:4',
            'minimum_amount' => 'decimal:8',
            'maximum_amount' => 'decimal:8',
            'min_amount_usd' => 'decimal:2',
            'max_amount_usd' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function resolvedLogoUrl(): ?string
    {
        if (filled($this->logo_url)) {
            return $this->logo_url;
        }

        $id = $this->coingecko_id ?: config('crypto.assets.'.strtoupper((string) $this->asset));
        $logo = $id ? config('crypto.logos.'.$id) : null;

        return is_string($logo) && $logo !== '' ? $logo : null;
    }

    /**
     * Per-coin spread (₦ below market). Falls back to OTC default / config when unset.
     */
    public function resolvedSpreadNgn(?float $fallback = null): float
    {
        if ($this->spread_ngn !== null && (float) $this->spread_ngn >= 0) {
            return (float) $this->spread_ngn;
        }

        if ($fallback !== null && $fallback >= 0) {
            return $fallback;
        }

        return self::defaultSpreadNgn();
    }

    /**
     * Buy rate = market − coin spread (₦/$1).
     */
    public function calculatedBuyRatePerUsd(float $marketUsdNgn, ?float $fallbackSpread = null): ?float
    {
        if ($marketUsdNgn <= 0) {
            return null;
        }

        $spread = $this->resolvedSpreadNgn($fallbackSpread);
        $rate = max(0, $marketUsdNgn - $spread);
        if ($rate <= 0 || $rate > self::maxBuyRatePerUsd()) {
            return null;
        }

        return round($rate, 2);
    }

    public function buyRateIsCorrupt(): bool
    {
        $rate = (float) ($this->sell_rate_ngn ?? 0);

        return $rate > self::maxBuyRatePerUsd();
    }

    /**
     * Cached/mirrored sell_rate when valid; otherwise null (prefer calculatedBuyRatePerUsd).
     */
    public function effectiveBuyRatePerUsd(): ?float
    {
        $rate = (float) ($this->sell_rate_ngn ?? 0);
        if ($rate <= 0 || $this->buyRateIsCorrupt()) {
            return null;
        }

        return $rate;
    }

    /**
     * @return list<string>
     */
    public function resolvedNetworkIds(): array
    {
        $stored = $this->allowed_network_ids;

        if (is_array($stored)) {
            return array_values(array_filter($stored, fn ($id) => is_string($id) && $id !== ''));
        }

        $map = config('crypto.network_ids_by_coin', []);
        $list = $map[strtoupper((string) $this->asset)] ?? [];

        return array_values(array_filter($list, fn ($id) => is_string($id) && $id !== ''));
    }
}
