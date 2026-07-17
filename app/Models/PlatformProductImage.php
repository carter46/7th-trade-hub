<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformProductImage extends Model
{
    protected $fillable = [
        'platform_product_id',
        'path',
        'alt',
        'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(PlatformProduct::class, 'platform_product_id');
    }
}
