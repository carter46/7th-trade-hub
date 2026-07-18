<?php

namespace Tests\Feature\Marketplace;

use App\Models\Listing;
use Database\Seeders\MarketplaceListingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceVendorsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_shows_sell_now_and_sample_vendors(): void
    {
        $this->seed(MarketplaceListingSeeder::class);

        $this->assertSame(50, Listing::query()->where('status', 'published')->where('is_active', true)->whereNotNull('user_id')->count());
        $this->assertDatabaseHas('users', ['email' => 'digitalvault@7thtrade.local', 'name' => 'DigitalVault']);

        $this->get(route('marketplace'))
            ->assertOk()
            ->assertSee('Discover digital products and online services from trusted vendors')
            ->assertSee('Want to be a vendor?')
            ->assertSee('Sell Now')
            ->assertSee('NextGen Media');
    }
}
