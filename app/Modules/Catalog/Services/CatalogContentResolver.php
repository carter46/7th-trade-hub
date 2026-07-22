<?php

namespace App\Modules\Catalog\Services;

use App\Models\CatalogPageContent;
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
        $db = CatalogPageContent::query()
            ->where('scope', 'group')
            ->where('key', $slug)
            ->first();

        return $this->merge($config, $db, [
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
        $db = CatalogPageContent::query()
            ->where('scope', 'group')
            ->where('key', $category->slug)
            ->first();

        $types = $category->relationLoaded('services')
            ? $category->services->pluck('slug')->all()
            : $category->services()->pluck('slug')->all();

        $base = [
            'label' => $category->name,
            'short_description' => $category->short_description ?: ($config['short_description'] ?? null),
            'hero_title' => $category->hero_title ?: ($config['hero_title'] ?? $category->name),
            'hero_subtitle' => $category->hero_subtitle ?: ($config['hero_subtitle'] ?? null),
            'banner_image' => $this->resolveImage(
                $category->bannerMedia,
                $category->banner_image,
                $db,
                'banner',
                'large',
                $config['banner_image'] ?? null,
            ),
            'card_image' => $this->resolveImage(
                $category->cardMedia,
                $category->card_image,
                $db,
                'card',
                'medium',
                $config['card_image'] ?? null,
            ),
            'benefits' => $category->benefits ?: ($config['benefits'] ?? []),
            'faq' => $category->faq ?: ($config['faq'] ?? []),
        ];

        return $this->mergeTextFields($base, $db, [
            'label' => $category->name,
            'types' => $types !== [] ? $types : ($config['types'] ?? []),
            'icon' => null,
            'mode' => $category->mode,
            'cta_label' => $category->cta_label,
        ]);
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
        $db = CatalogPageContent::query()
            ->where('scope', 'type')
            ->where('key', $type)
            ->first();

        return $this->merge($config, $db, [
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
        $db = CatalogPageContent::query()
            ->where('scope', 'type')
            ->where('key', $service->slug)
            ->first();

        $base = [
            'label' => $service->name,
            'short_description' => $service->short_description ?: ($config['short_description'] ?? null),
            'hero_title' => $service->hero_title ?: ($config['hero_title'] ?? $service->name),
            'hero_subtitle' => $service->hero_subtitle ?: ($config['hero_subtitle'] ?? null),
            'banner_image' => $this->resolveImage(
                $service->bannerMedia,
                $service->banner_image,
                $db,
                'banner',
                'large',
                $config['banner_image'] ?? null,
            ),
            'card_image' => $this->resolveImage(
                $service->cardMedia,
                $service->card_image,
                $db,
                'card',
                'medium',
                $config['card_image'] ?? null,
            ),
            'benefits' => $service->benefits ?: ($config['benefits'] ?? []),
            'faq' => $service->faq ?: ($config['faq'] ?? []),
            'icon' => $config['icon'] ?? 'grid',
        ];

        return $this->mergeTextFields($base, $db, [
            'label' => $service->name,
            'icon' => $config['icon'] ?? 'grid',
            'default_route' => $config['default_route'] ?? 'services',
            'types' => [$service->slug],
        ]);
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
     * Image priority (public storefront):
     * 1. Catalog Pages media picker override (banner_media_id / card_media_id)
     * 2. Service category / service media picker + legacy path columns
     * 3. Catalog Pages legacy path string (only when entity has no image)
     * 4. config/catalog.php defaults
     *
     * Stale catalog_page_contents.banner_image strings must NOT override a newly
     * uploaded Media Library image on the entity.
     */
    protected function resolveImage(
        ?MediaAsset $entityMedia,
        ?string $entityPath,
        ?CatalogPageContent $db,
        string $field,
        string $variant,
        ?string $configFallback,
    ): ?string {
        if ($db) {
            $db->loadMissing(['bannerMedia.variants', 'cardMedia.variants']);
        }

        $mediaIdAttr = $field.'_media_id';
        $pathAttr = $field.'_image';
        $relation = $field === 'banner' ? 'bannerMedia' : 'cardMedia';

        if ($db?->{$mediaIdAttr}) {
            $override = media_url($db->{$relation}, null, $variant);
            if ($override) {
                return $override;
            }
        }

        $fromEntity = media_url($entityMedia, $entityPath, $variant);
        if ($fromEntity) {
            return $fromEntity;
        }

        if ($db?->{$pathAttr}) {
            $fromDbPath = media_url(null, $db->{$pathAttr}, $variant);
            if ($fromDbPath) {
                return $fromDbPath;
            }
        }

        return $configFallback ? media_url(null, $configFallback, $variant) : null;
    }

    /**
     * Merge text/list overrides from Catalog Pages without touching images
     * (images are resolved separately via resolveImage).
     *
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $extras
     * @return array<string, mixed>
     */
    private function mergeTextFields(array $base, ?CatalogPageContent $db, array $extras): array
    {
        $pick = function (string $field, mixed $fallback = null) use ($base, $db) {
            $dbValue = $db?->{$field};
            if ($dbValue !== null && $dbValue !== '' && $dbValue !== []) {
                return $dbValue;
            }

            return $base[$field] ?? $fallback;
        };

        $label = $extras['label'] ?? ($base['label'] ?? '');
        $heroTitle = $pick('hero_title', $label);

        return array_merge([
            'label' => $label,
            'short_description' => $pick('short_description'),
            'hero_title' => is_string($heroTitle) ? $heroTitle : $label,
            'hero_subtitle' => $pick('hero_subtitle'),
            'banner_image' => $base['banner_image'] ?? null,
            'card_image' => $base['card_image'] ?? null,
            'benefits' => array_values($pick('benefits', []) ?: []),
            'faq' => array_values($pick('faq', []) ?: []),
        ], $extras);
    }

    /**
     * Config-only merge (no entity media). Catalog Pages media_id wins; else paths.
     *
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $extras
     * @return array<string, mixed>
     */
    private function merge(array $config, ?CatalogPageContent $db, array $extras): array
    {
        if ($db) {
            $db->loadMissing(['bannerMedia.variants', 'cardMedia.variants']);
        }

        $pick = function (string $field, mixed $fallback = null) use ($config, $db) {
            $dbValue = $db?->{$field};
            if ($dbValue !== null && $dbValue !== '' && $dbValue !== []) {
                return $dbValue;
            }

            return $config[$field] ?? $fallback;
        };

        $label = $extras['label'] ?? ($config['label'] ?? '');
        $heroTitle = $pick('hero_title', $label);

        $banner = $this->resolveImage(
            null,
            $config['banner_image'] ?? null,
            $db,
            'banner',
            'large',
            null,
        );
        $card = $this->resolveImage(
            null,
            $config['card_image'] ?? null,
            $db,
            'card',
            'medium',
            null,
        );

        return array_merge([
            'label' => $label,
            'short_description' => $pick('short_description'),
            'hero_title' => is_string($heroTitle) ? $heroTitle : $label,
            'hero_subtitle' => $pick('hero_subtitle'),
            'banner_image' => $banner ?: ($config['banner_image'] ?? null),
            'card_image' => $card ?: ($config['card_image'] ?? null),
            'benefits' => array_values($pick('benefits', []) ?: []),
            'faq' => array_values($pick('faq', []) ?: []),
        ], $extras);
    }
}
