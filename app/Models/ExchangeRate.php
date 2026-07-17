<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'asset',
        'buy_rate_ngn',
        'sell_rate_ngn',
        'minimum_amount',
        'maximum_amount',
        'processing_time',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'buy_rate_ngn' => 'decimal:2',
            'sell_rate_ngn' => 'decimal:2',
            'minimum_amount' => 'decimal:8',
            'maximum_amount' => 'decimal:8',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
