<?php

namespace App\Support\Domains;

use App\Models\PlatformProduct;

class DomainProductTldPolicy
{
    /**
     * @return list<string>
     */
    public static function defaultFeaturedTlds(): array
    {
        /** @var list<string> $configured */
        $configured = config('domains.default_product_tlds', []);

        return self::normalizeList($configured);
    }

    /**
     * @return list<string>
     */
    public static function allowedTlds(PlatformProduct $product): array
    {
        $meta = $product->meta ?? [];
        $stored = $meta['allowed_tlds'] ?? null;

        if (is_array($stored) && $stored !== []) {
            return self::normalizeList($stored);
        }

        return self::defaultFeaturedTlds();
    }

    /**
     * Primary extensions shown in the customer dropdown.
     *
     * @return list<string>
     */
    public static function featuredTlds(PlatformProduct $product): array
    {
        $allowed = self::allowedTlds($product);
        $preset = self::defaultFeaturedTlds();
        $featured = array_values(array_intersect($preset, $allowed));

        if ($featured === []) {
            return $allowed;
        }

        return $featured;
    }

    /**
     * Additional allowed extensions revealed via advanced search.
     *
     * @return list<string>
     */
    public static function advancedTlds(PlatformProduct $product): array
    {
        $allowed = self::allowedTlds($product);
        $featured = self::featuredTlds($product);

        return array_values(array_diff($allowed, $featured));
    }

    public static function isAllowed(PlatformProduct $product, string $tld): bool
    {
        $tld = ltrim(strtolower(trim($tld)), '.');

        return in_array($tld, self::allowedTlds($product), true);
    }

    /**
     * @param  list<string>|array<int, string>  $tlds
     * @return list<string>
     */
    public static function normalizeList(array $tlds): array
    {
        $normalized = [];

        foreach ($tlds as $tld) {
            if (! is_string($tld)) {
                continue;
            }

            $value = ltrim(strtolower(trim($tld)), '.');
            if ($value === '' || isset($normalized[$value])) {
                continue;
            }

            $normalized[$value] = $value;
        }

        return array_values($normalized);
    }
}
