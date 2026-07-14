<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_xml_with_static_and_listing_urls(): void
    {
        Listing::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Sitemap Listing',
            'slug' => 'sitemap-listing',
            'price' => 1000,
            'status' => 'published',
            'is_active' => true,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee(route('home'), false);
        $response->assertSee(route('marketplace'), false);
        $response->assertSee(route('marketplace.show', 'sitemap-listing'), false);
    }
}
