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

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Crypto Cash Exchange', false)
            ->assertSee('Website Packages', false)
            ->assertSee('Hosted website packages with demos and support windows.', false)
            ->assertDontSee('Website Listings', false)
            ->assertDontSee('Buy or sell websites with escrow to protect both sides.', false);

        $this->assertLessThanOrEqual(
            \App\Modules\Catalog\Services\CatalogBrowseService::HOME_ECOSYSTEM_LIMIT,
            substr_count($response->getContent(), 'Learn More')
        );
    }

    public function test_home_ecosystem_catalog_services_follow_admin_sort_order(): void
    {
        \Illuminate\Support\Facades\Artisan::call('catalog:backfill-hierarchy');
        $this->seed(\Database\Seeders\PlatformCatalogSeeder::class);
        \Illuminate\Support\Facades\Artisan::call('catalog:backfill-hierarchy');

        $vpn = \App\Models\ProductType::query()->where('slug', 'vpn')->firstOrFail();
        $email = \App\Models\ProductType::query()->where('slug', 'email')->firstOrFail();

        $vpn->forceFill(['sort_order' => 20])->save();
        $email->forceFill(['sort_order' => 2])->save();

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'Email Services'),
            strpos($html, 'VPN')
        );
        $this->assertStringContainsString('Crypto Cash Exchange', $html);
    }

    public function test_home_shows_pwa_install_section(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Your hub, anywhere', false)
            ->assertSee('Download Mobile app', false)
            ->assertSee('Download Desktop app', false)
            ->assertSee('No APK file required', false)
            ->assertSee('rounded-[2.5rem]', false)
            ->assertSee('lg:hidden', false)
            ->assertSee('hidden lg:block', false)
            ->assertSee('data-reveal="fade-up"', false)
            ->assertSee('reveal-fade-up', false)
            ->assertDontSee('Download APK', false)
            ->assertSee('data-pwa-install="mobile"', false)
            ->assertSee('data-pwa-install="desktop"', false)
            ->assertSee('tablet-black copy.png', false)
            ->assertSee('pwa-install-modal', false);
    }
}
