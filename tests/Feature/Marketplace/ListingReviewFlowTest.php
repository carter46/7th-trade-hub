<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_submit_draft_and_admin_can_publish(): void
    {
        $seller = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($seller);
        $seller->refresh();

        $this->actingAs($seller)
            ->post(route('dashboard.listings.store'), [
                'title' => 'My Service',
                'description' => 'A great service',
                'price' => 2500,
            ])
            ->assertRedirect(route('dashboard.listings'));

        $listing = $seller->listings()->first();
        $this->assertNotNull($listing);
        $this->assertSame('draft', $listing->status);
        $this->assertFalse($listing->is_active);

        $this->actingAs($seller)
            ->post(route('dashboard.listings.submit', $listing))
            ->assertRedirect();

        $listing->refresh();
        $this->assertSame('pending_review', $listing->status);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.listings.approve', $listing))
            ->assertRedirect();

        $listing->refresh();
        $this->assertSame('published', $listing->status);
        $this->assertTrue($listing->is_active);

        $this->get(route('marketplace.show', $listing->slug))
            ->assertOk()
            ->assertSee('My Service');
    }
}
