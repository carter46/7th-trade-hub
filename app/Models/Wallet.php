<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance_usd',
        'crypto_btc',
        'crypto_eth',
        'balance_change_label',
    ];

    protected function casts(): array
    {
        return [
            'balance_usd' => 'decimal:2',
            'crypto_btc' => 'decimal:8',
            'crypto_eth' => 'decimal:8',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
