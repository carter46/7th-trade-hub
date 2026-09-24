<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainRegistration extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PENDING_MANUAL = 'pending_manual';

    public const STATUS_PENDING_REPLACEMENT = 'pending_replacement';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_REGISTERED = 'registered';

    public const STATUS_REJECTED = 'rejected';

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

    public function isPendingManual(): bool
    {
        return $this->status === self::STATUS_PENDING_MANUAL;
    }

    public function isPendingReplacement(): bool
    {
        return $this->status === self::STATUS_PENDING_REPLACEMENT;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function awaitsManualAdminAction(): bool
    {
        return $this->isManualFulfillment()
            && in_array($this->status, [
                self::STATUS_PENDING_MANUAL,
                self::STATUS_PENDING_REPLACEMENT,
            ], true);
    }

    public function canRequestReplacement(): bool
    {
        return $this->isManualFulfillment()
            && ($this->isRejected() || $this->isPendingReplacement());
    }

    public function isManualFulfillment(): bool
    {
        if ($this->provider_key === DomainQuote::PROVIDER_KEY_MANUAL) {
            return true;
        }

        $meta = $this->provider_meta ?? [];

        return ($meta['fulfillment'] ?? null) === 'manual'
            || ($meta['domain_fulfillment'] ?? null) === 'manual';
    }

    public function rejectedFqdn(): ?string
    {
        $meta = $this->provider_meta ?? [];
        $value = $meta['rejected_fqdn'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function rejectionReason(): ?string
    {
        if ($this->isRejected() || $this->isPendingReplacement()) {
            return $this->error_message;
        }

        $meta = $this->provider_meta ?? [];
        $value = $meta['rejection_reason'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @return list<array{fqdn: string, rejected_at: string, reason: string}>
     */
    public function replacementHistory(): array
    {
        $history = $this->provider_meta['replacement_history'] ?? [];

        return is_array($history) ? array_values($history) : [];
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
