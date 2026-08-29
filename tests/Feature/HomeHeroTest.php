<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_hero_uses_three_fading_background_images(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('assets/images/homeslider1.jpg', false)
            ->assertSee('assets/images/homeslider2.jpg', false)
            ->assertSee('assets/images/homeslider3.jpg', false)
            ->assertSee('transition-opacity duration-1000', false)
            ->assertSee(route('services'), false);
    }

    public function test_home_ecosystem_uses_catalog_service_names_not_hardcoded_categories(): void
    {
        \Illuminate\Support\Facades\Artisan::call('catalog:backfill-hierarchy');
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);
        \Illuminate\Support\Facades\Artisan::call('catalog:backfill-hierarchy');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Crypto Cash Exchange', false)
            ->assertSee('Website Packages', false)
            ->assertSee('Hosted website packages with demos and support windows.', false)
            ->assertDontSee('Website Listings', false)
            ->assertDontSee('Buy or sell websites with escrow to protect both sides.', false);
    }
}
