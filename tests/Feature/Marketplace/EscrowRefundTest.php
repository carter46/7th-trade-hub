<?php

namespace Tests\Feature\Marketplace;

use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscrowRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_refund_returns_locked_funds_to_buyer(): void
    {
        $seller = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($seller);

        $buyer = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $buyer->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($buyer);
        $buyer->refresh();

        $listing = Listing::create([
            'user_id' => $seller->id,
            'title' => 'Refundable Item',
            'slug' => 'refundable-'.uniqid(),
            'price' => 1500,
            'status' => 'published',
            'is_active' => true,
        ]);

        app(WalletService::class)->adminAdjust($buyer->wallet, 5000, 'Test credit', 1);

        $this->actingAs($buyer)
            ->post(route('dashboard.checkout.store', $listing));

        $order = $buyer->orders()->first();
        $escrow = Escrow::where('order_id', $order->id)->first();

        $buyer->wallet->refresh();
        $this->assertEquals(3500.0, (float) $buyer->wallet->balance);
        $this->assertEquals(1500.0, (float) $buyer->wallet->locked_balance);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.escrows.refund', $escrow), ['reason' => 'Buyer dispute'])
            ->assertRedirect();

        $buyer->wallet->refresh();
        $escrow->refresh();
        $order->refresh();

        $this->assertEquals(5000.0, (float) $buyer->wallet->balance);
        $this->assertEquals(0.0, (float) $buyer->wallet->locked_balance);
        $this->assertSame('refunded', $escrow->status);
        $this->assertSame('cancelled', $order->status);
    }
}
