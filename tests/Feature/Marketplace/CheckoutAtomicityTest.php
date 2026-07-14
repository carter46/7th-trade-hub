<?php

namespace Tests\Feature\Marketplace;

use App\Models\Escrow;
use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAtomicityTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_creates_order_escrow_and_links_ledger(): void
    {
        $seller = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($seller);

        $buyer = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $buyer->assignRole('user');
        $buyerWallet = app(WalletProvisioningService::class)->createWallet($buyer);
        app(WalletService::class)->adminAdjust($buyerWallet, 10000, 'seed', 1);

        $listing = Listing::create([
            'user_id' => $seller->id,
            'title' => 'Atomic Item',
            'slug' => 'atomic-'.uniqid(),
            'price' => 2500,
            'status' => 'published',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)->post(route('dashboard.checkout.store', $listing));

        $order = $buyer->orders()->first();
        $this->assertNotNull($order);

        $escrow = Escrow::where('order_id', $order->id)->first();
        $this->assertNotNull($escrow);
        $this->assertSame('locked', $escrow->status);

        $lockTx = Transaction::where('order_id', $order->id)->first();
        $this->assertNotNull($lockTx);
        $this->assertSame($escrow->id, $lockTx->escrow_id);
    }
}
