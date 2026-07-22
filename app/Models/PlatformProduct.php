<?php

namespace App\Models;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Schema;

class PlatformProduct extends Model
{
    protected $fillable = [
        'platform_category_id',
        'product_type_id',
        'product_type',
        'title',
        'slug',
        'short_description',
        'description',
        'status',
        'is_featured',
        'sort_order',
        'hero_image',
        'hero_media_id',
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
        'provider',
        'provider_product_id',
        'provider_sku',
        'provider_meta',
        'fulfillment_mode',
        'auto_renew',
    ];

    protected function casts(): array
    {
        return [
            'product_type' => PlatformProductType::class,
            'status' => PlatformProductStatus::class,
            'is_featured' => 'boolean',
            'is_responsive' => 'boolean',
            'is_seo_ready' => 'boolean',
            'auto_renew' => 'boolean',
            'features' => 'array',
            'requirements' => 'array',
            'whats_included' => 'array',
            'faqs' => 'array',
            'meta' => 'array',
            'provider_meta' => 'array',
            'base_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlatformCategory::class, 'platform_category_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'hero_media_id');
    }

    /** UI alias: Service under Service Category. */
    public function service(): BelongsTo
    {
        return $this->productType();
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

        return $query->where(function (Builder $inner) use ($value) {
            $inner->where('product_type', $value);
            if (Schema::hasColumn('platform_products', 'product_type_id')) {
                $inner->orWhereHas('productType', fn (Builder $q) => $q->where('slug', $value));
            }
        });
    }

    /**
     * @param  list<string>  $types
     */
    public function scopeOfTypeMany(Builder $query, array $types): Builder
    {
        if ($types === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $inner) use ($types) {
            $inner->whereIn('product_type', $types);
            if (Schema::hasColumn('platform_products', 'product_type_id')) {
                $inner->orWhereHas('productType', fn (Builder $q) => $q->whereIn('slug', $types));
            }
        });
    }

    public function scopeOfService(Builder $query, int|ProductType $service): Builder
    {
        $id = $service instanceof ProductType ? $service->id : $service;

        return $query->where('product_type_id', $id);
    }

    public function typeSlug(): ?string
    {
        if ($this->relationLoaded('productType') && $this->productType) {
            return $this->productType->slug;
        }

        if ($this->product_type_id) {
            return ProductType::query()->where('id', $this->product_type_id)->value('slug');
        }

        $type = $this->product_type;

        return $type instanceof PlatformProductType ? $type->value : $type;
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
