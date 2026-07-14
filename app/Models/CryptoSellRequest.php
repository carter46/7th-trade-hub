<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CryptoSellRequest extends Model
{
    protected $fillable = [
        'user_id', 'wallet_id', 'coin', 'network', 'amount_crypto',
        'quoted_rate_ngn', 'expected_ngn', 'quoted_at', 'expires_at',
        'status', 'tx_hash', 'platform_address', 'wallet_funding_id', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_crypto' => 'decimal:8',
            'quoted_rate_ngn' => 'decimal:2',
            'expected_ngn' => 'decimal:2',
            'quoted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function walletFunding(): BelongsTo
    {
        return $this->belongsTo(WalletFunding::class);
    }

    public function isQuoteExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
