<?php

namespace App\Support;

use App\Models\PlatformProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformCatalogTrim
{
    /**
     * Remove platform products whose slug is not in config/platform_products.php for their type.
     *
     * @return array<string, int> type slug => deleted count
     */
    public static function retireDisallowedProducts(): array
    {
        if (! Schema::hasTable('platform_products')) {
            return [];
        }

        $allowed = config('platform_products', []);
        $removed = [];

        foreach ($allowed as $typeSlug => $keepSlugs) {
            if (! is_array($keepSlugs) || $keepSlugs === []) {
                continue;
            }

            $query = DB::table('platform_products')
                ->where('product_type', $typeSlug)
                ->whereNotIn('slug', $keepSlugs);

            $productIds = $query->pluck('id');

            if ($productIds->isEmpty()) {
                $removed[$typeSlug] = 0;

                continue;
            }

            if (Schema::hasTable('favorites')) {
                DB::table('favorites')
                    ->where('favoritable_type', PlatformProduct::class)
                    ->whereIn('favoritable_id', $productIds)
                    ->delete();
            }

            $removed[$typeSlug] = DB::table('platform_products')
                ->whereIn('id', $productIds)
                ->delete();
        }

        return $removed;
    }
}
