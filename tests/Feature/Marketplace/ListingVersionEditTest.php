<?php

namespace Tests\Feature\Marketplace;

use App\Models\Listing;
use App\Models\ListingVersion;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingVersionEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_edit_draft_and_resubmit_after_reject(): void
    {
        $seller = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($seller);

        $leafCategoryId = \App\Models\Category::query()->whereDoesntHave('children')->value('id');

        $listing = Listing::create([
            'user_id' => $seller->id,
            'category_id' => $leafCategoryId,
            'title' => 'Original',
            'slug' => 'original-'.uniqid(),
            'price' => 1000,
            'status' => 'rejected',
            'is_active' => false,
        ]);

        ListingVersion::create([
            'listing_id' => $listing->id,
            'version_number' => 1,
            'title' => 'Original',
            'price' => 1000,
            'status' => 'rejected',
        ]);

        $this->actingAs($seller)
            ->put(route('dashboard.listings.update', $listing), [
                'title' => 'Improved Title',
                'description' => 'Better description',
                'price' => 1200,
                'category_id' => $leafCategoryId,
            ])
            ->assertRedirect(route('dashboard.listings'));

        $this->actingAs($seller)
            ->post(route('dashboard.listings.submit', $listing))
            ->assertRedirect();

        $listing->refresh();
        $this->assertSame('pending_review', $listing->status);
        $this->assertDatabaseHas('listing_versions', [
            'listing_id' => $listing->id,
            'title' => 'Improved Title',
            'status' => 'pending_review',
        ]);
    }
}
