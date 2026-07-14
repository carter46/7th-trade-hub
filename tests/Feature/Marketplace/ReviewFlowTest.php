<?php

namespace Tests\Feature\Marketplace;

use App\Models\Listing;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Models\UserNotification;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_review_completed_order(): void
    {
        $seller = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $buyer->assignRole('user');

        $listing = Listing::create([
            'user_id' => $seller->id,
            'title' => 'Reviewable Item',
            'slug' => 'reviewable-'.uniqid(),
            'price' => 500,
            'status' => 'published',
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'listing_id' => $listing->id,
            'reference' => 'ORD-REV-1',
            'amount' => 500,
            'status' => 'completed',
        ]);

        $this->actingAs($buyer)
            ->post(route('dashboard.orders.review', $order), [
                'rating' => 5,
                'comment' => 'Great service!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'listing_id' => $listing->id,
            'rating' => 5,
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $seller->id,
            'type' => 'review',
        ]);
    }
}
