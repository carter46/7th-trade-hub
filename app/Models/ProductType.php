<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    protected $fillable = [
        'service_category_id',
        'name',
        'slug',
        'sort_order',
        'is_active',
        'banner_image',
        'card_image',
        'banner_media_id',
        'card_media_id',
        'short_description',
        'hero_title',
        'hero_subtitle',
        'benefits',
        'faq',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'benefits' => 'array',
            'faq' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'banner_media_id');
    }

    public function cardMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'card_media_id');
    }

    /** Landscape thumb for admin lists — card image is source of truth. */
    public function listThumbnailUrl(): ?string
    {
        $media = $this->cardMedia ?? $this->bannerMedia;
        if ($media) {
            // Prefer uncropped variants so the full image shows in a wide cell.
            return $media->url('medium') ?? $media->url('small') ?? $media->thumbnailUrl();
        }

        return media_url(null, $this->card_image ?: $this->banner_image, 'medium');
    }

    /** Alias for admin/UI "Service" naming. */
    public function category(): BelongsTo
    {
        return $this->serviceCategory();
    }

    public function products(): HasMany
    {
        return $this->hasMany(PlatformProduct::class, 'product_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
