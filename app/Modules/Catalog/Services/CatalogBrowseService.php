<?php

namespace App\Modules\Catalog\Services;

use App\Enums\PlatformProductStatus;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use Illuminate\Support\Collection;

class CatalogBrowseService
{
    /** @return list<string> */
    public function groupSlugs(): array
    {
        return array_keys(config('catalog.groups', []));
    }

    /** @return list<string> */
    public function typeKeys(): array
    {
        return array_keys(config('catalog.types', []));
    }

    /** @return list<string> */
    public function allGroupTypeValues(): array
    {
        return collect(config('catalog.groups', []))
            ->flatMap(fn (array $group) => $group['types'] ?? [])
            ->unique()
            ->values()
            ->all();
    }

    public function isGroup(string $slug): bool
    {
        return isset(config('catalog.groups')[$slug]);
    }

    public function isType(string $key): bool
    {
        return isset(config('catalog.types')[$key]);
    }

    public function groupForType(string $type): ?string
    {
        foreach (config('catalog.groups', []) as $slug => $group) {
            if (in_array($type, $group['types'] ?? [], true)) {
                return $slug;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $types
     * @return array{count: int, from_price: ?float}
     */
    public function statsForTypes(array $types): array
    {
        if ($types === []) {
            return ['count' => 0, 'from_price' => null];
        }

        $count = PlatformProduct::query()
            ->published()
            ->whereIn('product_type', $types)
            ->count();

        $productMin = PlatformProduct::query()
            ->published()
            ->whereIn('product_type', $types)
            ->where('base_price', '>', 0)
            ->min('base_price');

        $variantMin = PlatformProductVariant::query()
            ->where('is_active', true)
            ->whereHas('product', function ($q) use ($types) {
                $q->where('status', PlatformProductStatus::Published)
                    ->whereIn('product_type', $types);
            })
            ->min('price');

        $candidates = array_filter([
            $productMin !== null ? (float) $productMin : null,
            $variantMin !== null ? (float) $variantMin : null,
        ], fn ($v) => $v !== null && $v > 0);

        return [
            'count' => $count,
            'from_price' => $candidates === [] ? null : min($candidates),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function groupCards(CatalogContentResolver $content): Collection
    {
        return collect(config('catalog.groups', []))->map(function (array $group, string $slug) use ($content) {
            $resolved = $content->forGroup($slug);
            $stats = $this->statsForTypes($group['types'] ?? []);

            return array_merge($resolved, [
                'slug' => $slug,
                'count' => $stats['count'],
                'from_price' => $stats['from_price'],
                'href' => route('services.segment', $slug),
            ]);
        })->values();
    }

    /**
     * @param  list<string>  $types
     * @return Collection<int, array<string, mixed>>
     */
    public function typeCards(array $types, CatalogContentResolver $content): Collection
    {
        return collect($types)->map(function (string $type) use ($content) {
            $resolved = $content->forType($type);
            $stats = $this->statsForTypes([$type]);

            return array_merge($resolved, [
                'slug' => $type,
                'count' => $stats['count'],
                'from_price' => $stats['from_price'],
                'href' => route('services.segment', $type),
            ]);
        })->values();
    }
};
