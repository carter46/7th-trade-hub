<?php

namespace App\Modules\Marketplace\Services;

use App\Models\Category;
use App\Models\MarketplaceProduct;
use App\Models\MediaAsset;

class MarketplaceContentResolver
{
    /**
     * @return array<string, mixed>
     */
    public function forCategory(Category $category): array
    {
        $category->loadMissing(['bannerMedia.variants', 'cardMedia.variants']);

        $cardImage = $this->resolveImage($category->cardMedia, $category->card_image, 'medium');
        $hasCardMedia = (bool) $category->card_media_id;
        $bannerImage = $hasCardMedia
            ? ($this->resolveImage($category->cardMedia, $category->card_image, 'large') ?: $cardImage)
            : ($this->resolveImage($category->bannerMedia, $category->banner_image, 'large') ?: $cardImage);

        $ogImage = $category->og_image
            ? media_url(null, $category->og_image, 'large')
            : ($bannerImage ?: $cardImage);

        return [
            'label' => $category->name,
            'short_description' => $category->short_description,
            'hero_title' => $category->hero_title ?: $category->name,
            'hero_subtitle' => $category->hero_subtitle,
            'banner_image' => $bannerImage,
            'card_image' => $cardImage,
            'benefits' => array_values($category->benefits ?? []),
            'faq' => array_values($category->faq ?? []),
            'icon' => $category->icon,
            'seo_title' => $category->seo_title,
            'seo_description' => $category->seo_description,
            'og_title' => $category->og_title,
            'og_description' => $category->og_description,
            'og_image' => $ogImage,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forProduct(MarketplaceProduct $product): array
    {
        $product->loadMissing(['bannerMedia.variants', 'cardMedia.variants', 'category']);

        $cardImage = $this->resolveImage($product->cardMedia, $product->card_image, 'medium');
        $hasCardMedia = (bool) $product->card_media_id;
        $bannerImage = $hasCardMedia
            ? ($this->resolveImage($product->cardMedia, $product->card_image, 'large') ?: $cardImage)
            : ($this->resolveImage($product->bannerMedia, $product->banner_image, 'large') ?: $cardImage);

        $ogImage = $product->og_image
            ? media_url(null, $product->og_image, 'large')
            : ($bannerImage ?: $cardImage);

        return [
            'label' => $product->name,
            'short_description' => $product->short_description,
            'hero_title' => $product->hero_title ?: $product->name,
            'hero_subtitle' => $product->hero_subtitle,
            'banner_image' => $bannerImage,
            'card_image' => $cardImage,
            'benefits' => array_values($product->benefits ?? []),
            'faq' => array_values($product->faq ?? []),
            'icon' => $product->icon,
            'seo_title' => $product->seo_title,
            'seo_description' => $product->seo_description,
            'og_title' => $product->og_title,
            'og_description' => $product->og_description,
            'og_image' => $ogImage,
        ];
    }

    protected function resolveImage(?MediaAsset $entityMedia, ?string $entityPath, string $variant): ?string
    {
        return media_url($entityMedia, $entityPath, $variant);
    }
}
