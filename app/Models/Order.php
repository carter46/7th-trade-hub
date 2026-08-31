<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'source',
        'user_id',
        'listing_id',
        'reference',
        'idempotency_key',
        'amount',
        'total_amount',
        'status',
        'payment_method',
        'payment_provider',
        'provider_payment_reference',
        'provider_transaction_reference',
        'checkout_url',
        'checkout_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'checkout_expires_at' => 'datetime',
        ];
    }

    public function isCheckoutExpired(): bool
    {
        return $this->checkout_expires_at !== null && $this->checkout_expires_at->isPast();
    }

    public function isAwaitingGatewayPayment(): bool
    {
        return $this->payment_method === 'gateway'
            && in_array($this->status, ['pending', 'processing'], true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listing(): BelongsTo
    {
        // Soft-deleted listings must still resolve for escrow/sales history.
        return $this->belongsTo(Listing::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function domainRegistrations(): HasMany
    {
        return $this->hasMany(DomainRegistration::class);
    }
}
