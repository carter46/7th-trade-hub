<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainManualTldPrice extends Model
{
    protected $fillable = [
        'platform_product_id',
        'tld',
        'retail_price',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'retail_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PlatformProduct::class, 'platform_product_id');
    }

    public function displayTld(): string
    {
        return '.'.$this->tld;
    }
}
