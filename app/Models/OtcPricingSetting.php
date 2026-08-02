<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtcPricingSetting extends Model
{
    public const MODE_LIVE_MINUS_SPREAD = 'live_minus_spread';

    public const MODE_MANUAL_CUSTOMER_RATE = 'manual_customer_rate';

    protected $fillable = [
        'mode',
        'market_provider',
        'market_rate_ngn',
        'cached_market_rate_ngn',
        'spread_ngn',
        'manual_customer_rate_ngn',
        'tolerance_percent',
        'quote_ttl_minutes',
        'max_orders_per_wallet',
        'market_synced_at',
        'last_source',
    ];

    protected function casts(): array
    {
        return [
            'market_rate_ngn' => 'decimal:4',
            'cached_market_rate_ngn' => 'decimal:4',
            'spread_ngn' => 'decimal:4',
            'manual_customer_rate_ngn' => 'decimal:4',
            'tolerance_percent' => 'decimal:4',
            'quote_ttl_minutes' => 'integer',
            'max_orders_per_wallet' => 'integer',
            'market_synced_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        $row = static::query()->first();
        if ($row) {
            return $row;
        }

        return static::query()->create([
            'mode' => self::MODE_LIVE_MINUS_SPREAD,
            'market_provider' => 'manual_reference',
            'market_rate_ngn' => 1420,
            'cached_market_rate_ngn' => 1420,
            'spread_ngn' => 25,
            'tolerance_percent' => 0.5,
            'quote_ttl_minutes' => 15,
            'max_orders_per_wallet' => (int) config('crypto.max_orders_per_wallet', 8),
            'market_synced_at' => now(),
            'last_source' => 'manual_reference',
        ]);
    }

    public function customerRate(): float
    {
        if ($this->mode === self::MODE_MANUAL_CUSTOMER_RATE) {
            return (float) ($this->manual_customer_rate_ngn ?? 0);
        }

        $market = (float) ($this->market_rate_ngn
            ?? $this->cached_market_rate_ngn
            ?? 0);

        return max(0, $market - (float) $this->spread_ngn);
    }
}
