<?php

namespace App\Models;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PlatformProduct extends Model
{
    protected $fillable = [
        'platform_category_id',
        'product_type',
        'title',
        'slug',
        'short_description',
        'description',
        'status',
        'is_featured',
        'sort_order',
        'hero_image',
        'demo_url',
        'demo_username',
        'demo_password',
        'industry',
        'framework',
        'is_responsive',
        'is_seo_ready',
        'support_period',
        'features',
        'requirements',
        'whats_included',
        'faqs',
        'support_text',
        'base_price',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'product_type' => PlatformProductType::class,
            'status' => PlatformProductStatus::class,
            'is_featured' => 'boolean',
            'is_responsive' => 'boolean',
            'is_seo_ready' => 'boolean',
            'features' => 'array',
            'requirements' => 'array',
            'whats_included' => 'array',
            'faqs' => 'array',
            'meta' => 'array',
            'base_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlatformCategory::class, 'platform_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PlatformProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PlatformProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PlatformProductStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfType(Builder $query, PlatformProductType|string $type): Builder
    {
        $value = $type instanceof PlatformProductType ? $type->value : $type;

        return $query->where('product_type', $value);
    }

    public function displayPrice(): float
    {
        $variants = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : $this->activeVariants()->get();

        $default = $variants->firstWhere('is_default', true)
            ?? $variants->sortBy('price')->first();

        return (float) ($default?->price ?? $this->base_price);
    }
}
