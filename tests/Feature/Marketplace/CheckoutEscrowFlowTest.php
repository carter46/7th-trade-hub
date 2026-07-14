<?php

namespace Tests\Feature\Marketplace;

use App\Models\Listing;
use App\Models\User;
use App\Models\Withdrawal;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutEscrowFlowTest extends TestCase
{
    use RefreshDatabase;

    private function userWithWallet(): User
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);
        $user->refresh();

        return $user;
    }

    public function test_purchase_creates_escrow_and_confirm_releases_to_seller(): void
    {
        $seller = $this->userWithWallet();
        $buyer = $this->userWithWallet();

        $listing = Listing::create([
            'user_id' => $seller->id,
            'title' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'description' => 'Description',
            'price' => 1000,
            'status' => 'published',
            'is_active' => true,
        ]);

        app(WalletService::class)->adminAdjust($buyer->wallet, 5000, 'Test credit', 1);

        $this->actingAs($buyer)
            ->post(route('dashboard.checkout.store', $listing))
            ->assertRedirect(route('dashboard.orders'));

        $buyer->wallet->refresh();
        $this->assertEquals(4000.0, (float) $buyer->wallet->balance);
        $this->assertEquals(1000.0, (float) $buyer->wallet->locked_balance);

        $order = $buyer->orders()->first();
        $this->assertNotNull($order);
        $this->assertSame('processing', $order->status);
        $this->assertDatabaseHas('escrows', [
            'order_id' => $order->id,
            'status' => 'locked',
            'amount' => 1000,
        ]);

        $this->actingAs($buyer)
            ->post(route('dashboard.orders.confirm', $order))
            ->assertRedirect();

        $seller->wallet->refresh();
        $buyer->wallet->refresh();
        $order->refresh();

        $this->assertSame('completed', $order->status);
        $this->assertEquals(0.0, (float) $buyer->wallet->locked_balance);
        $this->assertGreaterThan(0, (float) $seller->wallet->balance);
    }

    public function test_cannot_purchase_when_seller_has_no_wallet(): void
    {
        $seller = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');

        $buyer = $this->userWithWallet();

        $listing = Listing::create([
            'user_id' => $seller->id,
            'title' => 'No Wallet Listing',
            'slug' => 'no-wallet-'.uniqid(),
            'price' => 500,
            'status' => 'published',
            'is_active' => true,
        ]);

        app(WalletService::class)->adminAdjust($buyer->wallet, 5000, 'Test credit', 1);

        $this->actingAs($buyer)
            ->post(route('dashboard.checkout.store', $listing))
            ->assertRedirect(route('dashboard.deposit.create-bank'))
            ->assertSessionHas('error');
    }
}
