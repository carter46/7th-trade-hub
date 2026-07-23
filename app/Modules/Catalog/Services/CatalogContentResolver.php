<?php

namespace App\Modules\Catalog\Services;

use App\Models\MediaAsset;
use App\Models\PlatformCategory;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Schema;

class CatalogContentResolver
{
    /**
     * @return array{
     *     label: string,
     *     short_description: ?string,
     *     hero_title: string,
     *     hero_subtitle: ?string,
     *     banner_image: ?string,
     *     card_image: ?string,
     *     benefits: list<string>,
     *     faq: list<array{q: string, a: string}>,
     *     icon: ?string,
     *     types: list<string>
     * }
     */
    public function forGroup(string $slug): array
    {
        if (Schema::hasTable('service_categories')) {
            $category = ServiceCategory::query()
                ->with(['bannerMedia.variants', 'cardMedia.variants'])
                ->where('slug', $slug)
                ->first();
            if ($category) {
                return $this->forServiceCategory($category);
            }
        }

        $config = config('catalog.groups.'.$slug, []);

        return $this->fromConfig($config, [
            'label' => $config['label'] ?? str_replace('-', ' ', ucfirst($slug)),
            'types' => $config['types'] ?? [],
            'icon' => null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function forServiceCategory(ServiceCategory $category): array
    {
        $config = config('catalog.groups.'.$category->slug, []);

        $types = $category->relationLoaded('services')
            ? $category->services->pluck('slug')->all()
            : $category->services()->pluck('slug')->all();

        $cardImage = $this->resolveImage(
            $category->cardMedia,
            $category->card_image,
            'medium',
            $config['card_image'] ?? null,
        );

        $hasCardMedia = (bool) $category->card_media_id;
        $bannerImage = $hasCardMedia
            ? ($this->resolveImage($category->cardMedia, $category->card_image, 'large', $config['card_image'] ?? null) ?: $cardImage)
            : ($this->resolveImage($category->bannerMedia, $category->banner_image, 'large', $config['banner_image'] ?? null) ?: $cardImage);

        return [
            'label' => $category->name,
            'short_description' => $category->short_description ?: ($config['short_description'] ?? null),
            'hero_title' => $category->hero_title ?: ($config['hero_title'] ?? $category->name),
            'hero_subtitle' => $category->hero_subtitle ?: ($config['hero_subtitle'] ?? null),
            'card_image' => $cardImage,
            'banner_image' => $bannerImage,
            'benefits' => $category->benefits ?: ($config['benefits'] ?? []),
            'faq' => $category->faq ?: ($config['faq'] ?? []),
            'types' => $types !== [] ? $types : ($config['types'] ?? []),
            'icon' => null,
            'mode' => $category->mode,
            'cta_label' => $category->cta_label,
        ];
    }

    /**
     * @return array{
     *     label: string,
     *     short_description: ?string,
     *     hero_title: string,
     *     hero_subtitle: ?string,
     *     banner_image: ?string,
     *     card_image: ?string,
     *     benefits: list<string>,
     *     faq: list<array{q: string, a: string}>,
     *     icon: ?string,
     *     default_route: string,
     *     types: list<string>
     * }
     */
    public function forType(string $type): array
    {
        if (Schema::hasTable('product_types')) {
            $service = ProductType::query()
                ->with(['bannerMedia.variants', 'cardMedia.variants'])
                ->where('slug', $type)
                ->first();
            if ($service) {
                return $this->forService($service);
            }
        }

        $config = config('catalog.types.'.$type, []);

        return $this->fromConfig($config, [
            'label' => $config['label'] ?? str_replace('_', ' ', ucfirst($type)),
            'icon' => $config['icon'] ?? 'grid',
            'default_route' => $config['default_route'] ?? 'services',
            'types' => [$type],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function forService(ProductType $service): array
    {
        $config = config('catalog.types.'.$service->slug, []);

        $cardImage = $this->resolveImage(
            $service->cardMedia,
            $service->card_image,
            'medium',
            $config['card_image'] ?? null,
        );

        $hasCardMedia = (bool) $service->card_media_id;
        $bannerImage = $hasCardMedia
            ? ($this->resolveImage($service->cardMedia, $service->card_image, 'large', $config['card_image'] ?? null) ?: $cardImage)
            : ($this->resolveImage($service->bannerMedia, $service->banner_image, 'large', $config['banner_image'] ?? null) ?: $cardImage);

        return [
            'label' => $service->name,
            'short_description' => $service->short_description ?: ($config['short_description'] ?? null),
            'hero_title' => $service->hero_title ?: ($config['hero_title'] ?? $service->name),
            'hero_subtitle' => $service->hero_subtitle ?: ($config['hero_subtitle'] ?? null),
            'card_image' => $cardImage,
            'banner_image' => $bannerImage,
            'benefits' => $service->benefits ?: ($config['benefits'] ?? []),
            'faq' => $service->faq ?: ($config['faq'] ?? []),
            'icon' => $config['icon'] ?? 'grid',
            'default_route' => $config['default_route'] ?? 'services',
            'types' => [$service->slug],
        ];
    }

    /**
     * @return array{
     *     label: string,
     *     short_description: ?string,
     *     hero_title: string,
     *     hero_subtitle: ?string,
     *     banner_image: ?string,
     *     card_image: ?string,
     *     benefits: list<string>,
     *     faq: list<array{q: string, a: string}>,
     *     icon: ?string,
     *     types: list<string>
     * }
     */
    public function forCategory(PlatformCategory $category): array
    {
        $typeValue = $category->product_type instanceof \BackedEnum
            ? $category->product_type->value
            : (string) $category->product_type;
        $typeDefaults = config('catalog.types.'.$typeValue, []);

        $config = [
            'label' => $category->name,
            'short_description' => $category->short_description,
            'hero_title' => $category->hero_title ?: $category->name,
            'hero_subtitle' => $category->hero_subtitle,
            'banner_image' => media_url(null, $category->banner_image, 'large') ?: ($typeDefaults['banner_image'] ?? null),
            'card_image' => media_url(null, $category->card_image, 'medium') ?: ($typeDefaults['card_image'] ?? null),
            'benefits' => $category->benefits ?: [],
            'faq' => $category->faq ?: [],
            'icon' => $typeDefaults['icon'] ?? 'grid',
        ];

        foreach (['short_description', 'hero_subtitle', 'banner_image', 'card_image'] as $field) {
            if (blank($config[$field]) && ! blank($typeDefaults[$field] ?? null)) {
                $config[$field] = $typeDefaults[$field];
            }
        }
        if ($config['benefits'] === [] && ! empty($typeDefaults['benefits'])) {
            $config['benefits'] = $typeDefaults['benefits'];
        }
        if ($config['faq'] === [] && ! empty($typeDefaults['faq'])) {
            $config['faq'] = $typeDefaults['faq'];
        }

        return array_merge($config, [
            'types' => [$typeValue],
        ]);
    }

    /**
     * Image priority: entity media library → entity legacy path → config/catalog.php.
     * Card media is the source of truth for headers when present.
     */
    protected function resolveImage(
        ?MediaAsset $entityMedia,
        ?string $entityPath,
        string $variant,
        ?string $configFallback,
    ): ?string {
        $fromEntity = media_url($entityMedia, $entityPath, $variant);
        if ($fromEntity) {
            return $fromEntity;
        }

        return $configFallback ? media_url(null, $configFallback, $variant) : null;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $extras
     * @return array<string, mixed>
     */
    private function fromConfig(array $config, array $extras): array
    {
        $label = $extras['label'] ?? ($config['label'] ?? '');
        $card = $this->resolveImage(null, $config['card_image'] ?? null, 'medium', null);
        $banner = $this->resolveImage(null, $config['banner_image'] ?? null, 'large', null)
            ?: $this->resolveImage(null, $config['card_image'] ?? null, 'large', null)
            ?: $card;

        return array_merge([
            'label' => $label,
            'short_description' => $config['short_description'] ?? null,
            'hero_title' => $config['hero_title'] ?? $label,
            'hero_subtitle' => $config['hero_subtitle'] ?? null,
            'banner_image' => $banner ?: ($config['banner_image'] ?? $config['card_image'] ?? null),
            'card_image' => $card ?: ($config['card_image'] ?? null),
            'benefits' => array_values($config['benefits'] ?? []),
            'faq' => array_values($config['faq'] ?? []),
        ], $extras);
    }
}
