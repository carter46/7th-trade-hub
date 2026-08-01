<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_uses_absolute_sitemap_url(): void
    {
        $response = $this->get(route('robots'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $body = $response->getContent();
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Sitemap: '.rtrim(config('app.url'), '/').'/sitemap.xml', $body);
        $this->assertStringNotContainsString('Sitemap: /sitemap.xml', $body);
    }

    public function test_sitemap_returns_xml_with_static_and_listing_urls(): void
    {
        Cache::forget('sitemap.xml.v2');

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
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $response->assertSee(route('home'), false);
        $response->assertSee(route('marketplace'), false);
        $response->assertSee(route('marketplace.show', 'sitemap-listing'), false);
        $response->assertSee('<?xml version="1.0"', false);
    }
}
