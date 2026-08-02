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

    protected $fillable = [
        'asset',
        'coingecko_id',
        'bybit_symbol',
        'allowed_network_ids',
        'logo_url',
        'buy_rate_ngn',
        'sell_rate_ngn',
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

    public function buyRateIsCorrupt(): bool
    {
        $rate = (float) ($this->sell_rate_ngn ?? 0);

        return $rate > self::maxBuyRatePerUsd();
    }

    /**
     * Effective Our Buy Rate (₦/$1), or null when corrupt/unset.
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

        // Explicit array (including empty) is the source of truth once set.
        if (is_array($stored)) {
            return array_values(array_filter($stored, fn ($id) => is_string($id) && $id !== ''));
        }

        // Legacy rows without the column / null: fall back to config whitelist.
        $map = config('crypto.network_ids_by_coin', []);
        $list = $map[strtoupper((string) $this->asset)] ?? [];

        return array_values(array_filter($list, fn ($id) => is_string($id) && $id !== ''));
    }
}
