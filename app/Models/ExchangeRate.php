<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'asset',
        'coingecko_id',
        'bybit_symbol',
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
}
