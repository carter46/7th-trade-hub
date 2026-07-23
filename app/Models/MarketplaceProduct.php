<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceProduct extends Model
{
    /** @use HasFactory<\Database\Factories\MarketplaceProductFactory> */
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sort_order',
        'is_active',
        'short_description',
        'hero_title',
        'hero_subtitle',
        'benefits',
        'faq',
        'banner_image',
        'card_image',
        'banner_media_id',
        'card_media_id',
        'icon',
        'seo_title',
        'seo_description',
        'og_title',
        'og_description',
        'og_image',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
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
            return $media->url('medium') ?? $media->url('small') ?? $media->thumbnailUrl();
        }

        return media_url(null, $this->card_image ?: $this->banner_image, 'medium');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
