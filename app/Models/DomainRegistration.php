<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainRegistration extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_REGISTERED = 'registered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_RECONCILIATION_REQUIRED = 'reconciliation_required';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'domain_quote_id',
        'fqdn',
        'provider_key',
        'provider_cost_at_checkout',
        'provider_currency_at_checkout',
        'registrant_contact',
        'nameservers',
        'nameservers_updated_at',
        'nameservers_synced_at',
        'status',
        'retry_count',
        'last_attempt_at',
        'next_retry_at',
        'provider_reference',
        'error_message',
        'provider_meta',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_cost_at_checkout' => 'decimal:4',
            'registrant_contact' => 'array',
            'nameservers' => 'array',
            'provider_meta' => 'array',
            'registered_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function domainQuote(): BelongsTo
    {
        return $this->belongsTo(DomainQuote::class);
    }

    public function isRegistered(): bool
    {
        return $this->status === self::STATUS_REGISTERED;
    }

    /**
     * @return list<string>
     */
    public function nameserverList(): array
    {
        $list = $this->nameservers ?? [];

        return is_array($list) ? array_values(array_filter($list, fn ($ns) => is_string($ns) && $ns !== '')) : [];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForUser($query, int $userId): void
    {
        $query->whereHas('order', fn ($order) => $order->where('user_id', $userId));
    }
}
