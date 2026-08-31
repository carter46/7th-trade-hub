<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainQuote extends Model
{
    protected $fillable = [
        'user_id',
        'platform_product_id',
        'provider_key',
        'token_hash',
        'fqdn',
        'tld',
        'sld',
        'provider_cost',
        'provider_currency',
        'retail_price',
        'retail_currency',
        'premium',
        'purchase_type',
        'provider_meta',
        'expires_at',
        'reserved_at',
        'reserved_order_id',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_cost' => 'decimal:4',
            'retail_price' => 'decimal:2',
            'premium' => 'boolean',
            'provider_meta' => 'array',
            'expires_at' => 'datetime',
            'reserved_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PlatformProduct::class, 'platform_product_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(DomainProvider::class, 'provider_key', 'key');
    }

    public function reservedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'reserved_order_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function isReserved(): bool
    {
        return $this->reserved_at !== null && ! $this->isConsumed();
    }
}
