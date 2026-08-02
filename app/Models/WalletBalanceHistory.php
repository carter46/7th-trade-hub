<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletBalanceHistory extends Model
{
    protected $table = 'wallet_balance_history';

    protected $fillable = [
        'crypto_deposit_wallet_id',
        'balance',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:10',
            'recorded_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(CryptoDepositWallet::class, 'crypto_deposit_wallet_id');
    }
}
