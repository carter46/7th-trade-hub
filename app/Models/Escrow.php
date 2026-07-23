<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escrow extends Model
{
    /** @use HasFactory<\Database\Factories\EscrowFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id', 'buyer_wallet_id', 'seller_wallet_id', 'amount', 'status',
        'released_at', 'released_by', 'refunded_at', 'refund_amount',
        'reason', 'admin_notes', 'evidence_paths',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'released_at' => 'datetime',
            'refunded_at' => 'datetime',
            'evidence_paths' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function buyerWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'buyer_wallet_id');
    }

    public function sellerWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'seller_wallet_id');
    }
}
