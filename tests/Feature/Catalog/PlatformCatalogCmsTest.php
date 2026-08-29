<?php

namespace Tests\Feature\Catalog;

use App\Models\ProductType;
use App\Modules\Catalog\Services\CatalogContentResolver;
use App\Support\PlatformCatalogCms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformCatalogCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalizes_all_platform_services_including_social_media(): void
    {
        Artisan::call('catalog:backfill-hierarchy');

        $social = ProductType::query()->where('slug', 'social_service')->firstOrFail();
        $social->forceFill([
            'name' => 'Social Media Services',
            'short_description' => 'Growth and engagement packages.',
            'hero_title' => 'Manual Hero Title',
            'hero_subtitle' => 'Manual subtitle',
            'benefits' => ['Old benefit one', 'Old benefit two'],
        ])->save();

        PlatformCatalogCms::normalizeHeroAndBenefits();

        $social->refresh();
        $this->assertSame('Social Media Services', $social->hero_title);
        $this->assertSame('Growth and engagement packages.', $social->hero_subtitle);
        $this->assertSame([], $social->benefits);

        $resolved = app(CatalogContentResolver::class)->forService($social);
        $this->assertSame('Social Media Services', $resolved['hero_title']);
        $this->assertSame([], $resolved['benefits']);
    }
}
