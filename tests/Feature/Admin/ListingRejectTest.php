<?php

namespace Tests\Feature\Admin;

use App\Models\Listing;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingRejectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reject_pending_listing(): void
    {
        $seller = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($seller);

        $listing = Listing::create([
            'user_id' => $seller->id,
            'title' => 'Rejected Item',
            'slug' => 'rejected-item-'.uniqid(),
            'price' => 1500,
            'status' => 'pending_review',
            'is_active' => false,
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.listings.reject', $listing), ['notes' => 'Incomplete description'])
            ->assertRedirect();

        $listing->refresh();
        $this->assertSame('rejected', $listing->status);
        $this->assertFalse($listing->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'listing.rejected']);
    }
}
