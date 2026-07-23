<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_active',
        'parent_id',
        'sort_order',
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

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(MarketplaceProduct::class);
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

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMarketplace(Builder $query): Builder
    {
        return $query->where('type', 'marketplace');
    }

    public function scopeLeaves($query)
    {
        return $query->whereDoesntHave('children');
    }

    public function isLeaf(): bool
    {
        return ! $this->children()->exists();
    }
}
