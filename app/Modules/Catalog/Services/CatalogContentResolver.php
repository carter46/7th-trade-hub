<?php

namespace App\Modules\Catalog\Services;

use App\Models\CatalogPageContent;
use App\Models\PlatformCategory;

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
        $typeDefaults = config('catalog.types.'.$category->product_type->value, []);

        $config = [
            'label' => $category->name,
            'short_description' => $category->short_description,
            'hero_title' => $category->hero_title ?: $category->name,
            'hero_subtitle' => $category->hero_subtitle,
            'banner_image' => $category->banner_image,
            'card_image' => $category->card_image,
            'benefits' => $category->benefits ?? [],
            'faq' => $category->faq ?? [],
            'icon' => $typeDefaults['icon'] ?? 'grid',
        ];

        // Prefer non-empty category fields; fall back type config for empty arrays/nulls
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
            'types' => [$category->product_type->value],
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $extras
     * @return array<string, mixed>
     */
    private function merge(array $config, ?CatalogPageContent $db, array $extras): array
    {
        $pick = function (string $field, mixed $fallback = null) use ($config, $db) {
            $dbValue = $db?->{$field};
            if ($dbValue !== null && $dbValue !== '' && $dbValue !== []) {
                return $dbValue;
            }

            return $config[$field] ?? $fallback;
        };

        $label = $extras['label'] ?? ($config['label'] ?? '');
        $heroTitle = $pick('hero_title', $label);

        return array_merge([
            'label' => $label,
            'short_description' => $pick('short_description'),
            'hero_title' => is_string($heroTitle) ? $heroTitle : $label,
            'hero_subtitle' => $pick('hero_subtitle'),
            'banner_image' => $pick('banner_image'),
            'card_image' => $pick('card_image'),
            'benefits' => array_values($pick('benefits', []) ?: []),
            'faq' => array_values($pick('faq', []) ?: []),
        ], $extras);
    }
};
