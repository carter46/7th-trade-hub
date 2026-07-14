<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_support_ticket(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);

        $ticket = SupportTicket::create([
            'user_id' => $owner->id,
            'category' => 'wallet',
            'subject' => 'Test',
            'body' => 'Help',
            'status' => 'open',
        ]);

        $this->actingAs($other)
            ->get(route('dashboard.support.show', $ticket))
            ->assertForbidden();
    }

    public function test_user_cannot_confirm_another_users_order(): void
    {
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create(['email_verified_at' => now()]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'reference' => 'ORD-TEST-1',
            'amount' => 100,
            'status' => 'processing',
        ]);

        $this->actingAs($other)
            ->post(route('dashboard.orders.confirm', $order))
            ->assertForbidden();
    }

    public function test_user_cannot_buy_own_listing(): void
    {
        $seller = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');
        app(\App\Modules\Wallet\Services\WalletProvisioningService::class)->createWallet($seller);
        $seller->refresh();
        $this->assertNotNull($seller->wallet, 'Wallet must exist before checkout');

        $listing = Listing::create([
            'user_id' => $seller->id,
            'title' => 'Own item',
            'slug' => 'own-item-'.uniqid(),
            'price' => 500,
            'status' => 'published',
            'is_active' => true,
        ]);

        $this->assertSame($seller->id, (int) $listing->fresh()->user_id);
        $this->assertFalse($seller->can('purchase', $listing));

        $this->actingAs($seller)
            ->post(route('dashboard.checkout.store', $listing))
            ->assertForbidden();
    }
}
