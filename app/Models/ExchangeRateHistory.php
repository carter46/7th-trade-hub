<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRateHistory extends Model
{
    protected $table = 'exchange_rate_history';

    protected $fillable = [
        'market_rate_ngn',
        'spread_ngn',
        'customer_rate_ngn',
        'source',
        'meta',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'market_rate_ngn' => 'decimal:4',
            'spread_ngn' => 'decimal:4',
            'customer_rate_ngn' => 'decimal:4',
            'meta' => 'array',
            'recorded_at' => 'datetime',
        ];
    }
}
