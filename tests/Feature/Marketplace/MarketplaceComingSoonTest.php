<?php

namespace Tests\Feature\Marketplace;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceComingSoonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'marketplace.public_coming_soon' => true,
            'marketplace.dashboard_coming_soon' => true,
        ]);
    }

    public function test_public_marketplace_shows_coming_soon_page(): void
    {
        $this->get(route('marketplace'))
            ->assertOk()
            ->assertSee('Coming Soon', false)
            ->assertSee('Become a seller', false)
            ->assertSee(route('login'), false)
            ->assertDontSee('Browse listings', false);
    }

    public function test_public_marketplace_segment_also_shows_coming_soon(): void
    {
        $this->get(route('marketplace.show', 'any-segment'))
            ->assertOk()
            ->assertSee('Coming Soon', false);
    }

    public function test_dashboard_marketplace_routes_show_coming_soon(): void
    {
        $user = \App\Models\User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.marketplace'))
            ->assertOk()
            ->assertSee('Marketplace is on the way', false);

        $this->actingAs($user)
            ->get(route('dashboard.listings'))
            ->assertOk()
            ->assertSee('Marketplace is on the way', false);

        $this->actingAs($user)
            ->get(route('dashboard.watchlist'))
            ->assertOk()
            ->assertSee('Marketplace is on the way', false);
    }
}
