<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    protected $fillable = [
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
        'mode',
        'cta_label',
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

    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'banner_media_id');
    }

    public function cardMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'card_media_id');
    }

    /** Thumbnail for admin list tables (card preferred, then banner). */
    public function listThumbnailUrl(): ?string
    {
        $media = $this->cardMedia ?? $this->bannerMedia;
        if ($media) {
            return $media->thumbnailUrl() ?? $media->url('small') ?? $media->url('medium');
        }

        return media_url(null, $this->card_image ?: $this->banner_image, 'thumbnail');
    }

    /** Services (product_types) under this category. */
    public function services(): HasMany
    {
        return $this->hasMany(ProductType::class)->orderBy('sort_order');
    }

    public function productTypes(): HasMany
    {
        return $this->services();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isMarketplaceLink(): bool
    {
        return $this->mode === 'marketplace_link';
    }
}
