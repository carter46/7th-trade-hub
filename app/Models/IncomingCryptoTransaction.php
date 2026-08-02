<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingCryptoTransaction extends Model
{
    public const STATUS_DETECTED = 'detected';

    public const STATUS_MATCHED = 'matched';

    public const STATUS_READY = 'ready';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'coin',
        'network',
        'wallet_address',
        'tx_hash',
        'amount',
        'block_height',
        'confirmations',
        'from_address',
        'token_contract',
        'detected_at',
        'matched_order_id',
        'status',
        'raw',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:10',
            'block_height' => 'integer',
            'confirmations' => 'integer',
            'detected_at' => 'datetime',
            'raw' => 'array',
        ];
    }

    public function matchedOrder(): BelongsTo
    {
        return $this->belongsTo(CryptoSellRequest::class, 'matched_order_id');
    }
}
