<?php

namespace App\Support;

use App\Models\ProductType;
use App\Models\ServiceCategory;

class PlatformCatalogCms
{
    /**
     * Platform catalog hero copy is derived from name/description; benefits are not used.
     *
     * @return array{categories: int, services: int}
     */
    public static function normalizeHeroAndBenefits(): array
    {
        $categories = 0;
        $services = 0;

        foreach (ServiceCategory::query()->system()->cursor() as $category) {
            $category->forceFill([
                'hero_title' => $category->name,
                'hero_subtitle' => $category->short_description,
                'benefits' => [],
            ])->save();
            $categories++;
        }

        foreach (
            ProductType::query()
                ->whereHas('serviceCategory', fn ($q) => $q->system())
                ->cursor() as $service
        ) {
            $service->forceFill([
                'hero_title' => $service->name,
                'hero_subtitle' => $service->short_description,
                'benefits' => [],
            ])->save();
            $services++;
        }

        return [
            'categories' => $categories,
            'services' => $services,
        ];
    }
}
