<?php

namespace Tests\Feature\Dashboard;

use App\Models\Listing;
use App\Models\MarketplaceProduct;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoverHubsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_discover_marketplace_and_services(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);

        $product = MarketplaceProduct::query()->where('is_active', true)->first();
        $this->assertNotNull($product);

        Listing::factory()->published()->create([
            'user_id' => $user->id,
            'marketplace_product_id' => $product->id,
            'category_id' => $product->category_id,
            'title' => 'Discover Hub Listing',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.discover.marketplace'))
            ->assertOk()
            ->assertSee('Marketplace')
            ->assertSee('Discover Hub Listing');

        $this->actingAs($user)
            ->get(route('dashboard.discover.services'))
            ->assertOk()
            ->assertSee('Services');
    }
}
