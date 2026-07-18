<?php

namespace Tests\Feature\Marketplace;

use App\Models\Listing;
use Database\Seeders\MarketplaceListingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceAjaxFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_filter_returns_html_fragment(): void
    {
        $this->seed(MarketplaceListingSeeder::class);

        $this->getJson(route('marketplace', ['ajax' => 1, 'q' => 'Premium']))
            ->assertOk()
            ->assertJsonStructure(['html', 'url'])
            ->assertSee('Premium', false);
    }

    public function test_suggestions_return_matches_and_keywords(): void
    {
        $this->seed(MarketplaceListingSeeder::class);

        $this->getJson(route('marketplace.suggestions', ['q' => 'VPN']))
            ->assertOk()
            ->assertJsonStructure(['suggestions', 'keywords']);
    }

    public function test_seeded_titles_have_no_dash_separators(): void
    {
        $this->seed(MarketplaceListingSeeder::class);

        $titles = Listing::query()->pluck('title');
        foreach ($titles as $title) {
            $this->assertStringNotContainsString('—', $title);
            $this->assertStringNotContainsString(' - ', $title);
        }
    }
}
