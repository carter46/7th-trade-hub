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

        config(['marketplace.public_coming_soon' => true]);
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
}
