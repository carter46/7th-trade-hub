<?php

namespace App\Models;

use App\Enums\PlatformProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformCategory extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'product_type',
        'sort_order',
        'is_active',
        'banner_image',
        'card_image',
        'short_description',
        'hero_title',
        'hero_subtitle',
        'benefits',
        'faq',
    ];

    protected function casts(): array
    {
        return [
            'product_type' => PlatformProductType::class,
            'is_active' => 'boolean',
            'benefits' => 'array',
            'faq' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(PlatformProduct::class);
    }
}
