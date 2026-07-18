<?php

namespace Tests\Feature\Marketplace;

use App\Models\AuditLog;
use App\Models\KycSubmission;
use App\Models\Listing;
use App\Models\User;
use App\Models\WalletFunding;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullJourneyTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_complete_marketplace_money_loop_via_http(): void
    {
        $admin = $this->admin();

        // Seller: KYC → wallet → listing → publish
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $seller->assignRole('user');

        $this->actingAs($seller)
            ->post(route('dashboard.kyc.store'), [
                'document_type' => 'national_id',
                'document_number' => 'NGN-ID-001',
            ])
            ->assertRedirect();

        $submission = KycSubmission::where('user_id', $seller->id)->first();
        $this->assertNotNull($submission);

        $this->actingAs($admin)
            ->post(route('admin.kyc.approve', $submission))
            ->assertRedirect();

        $seller->refresh();
        $this->assertGreaterThanOrEqual(1, $seller->kyc_level);

        $this->actingAs($seller)
            ->post(route('dashboard.wallet.create'))
            ->assertRedirect(route('dashboard.wallet'));

        $seller->refresh();
        $this->assertNotNull($seller->wallet);

        $this->actingAs($seller)
            ->post(route('dashboard.listings.store'), [
                'title' => 'Full Journey Product',
                'description' => 'End-to-end test listing',
                'price' => 2000,
                'category_id' => \App\Models\Category::query()->whereDoesntHave('children')->value('id'),
            ])
            ->assertRedirect(route('dashboard.listings'));

        $listing = Listing::where('user_id', $seller->id)->first();
        $this->assertNotNull($listing);

        $this->actingAs($seller)
            ->post(route('dashboard.listings.submit', $listing))
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.listings.approve', $listing->fresh()))
            ->assertRedirect();

        $listing->refresh();
        $this->assertSame('published', $listing->status);

        // Buyer: wallet → deposit → purchase → confirm → review
        $buyer = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $buyer->assignRole('user');

        $this->actingAs($buyer)->post(route('dashboard.wallet.create'));
        $buyer->refresh();

        $this->actingAs($buyer)
            ->from(route('dashboard.deposit.create-bank'))
            ->post(route('dashboard.deposit.store-bank'), [
                'amount' => 10000,
                'bank_name' => 'GTBank',
                'transfer_reference' => 'E2E-DEP-001',
            ])
            ->assertRedirect(route('dashboard.deposit.index'));

        $funding = WalletFunding::where('user_id', $buyer->id)->first();
        $this->actingAs($admin)->post(route('admin.fundings.approve', $funding));

        $buyer->wallet->refresh();
        $this->assertEquals(10000.0, (float) $buyer->wallet->balance);

        $this->actingAs($buyer)
            ->post(route('dashboard.checkout.store', $listing))
            ->assertRedirect(route('dashboard.orders'));

        $order = $buyer->orders()->first();
        $this->assertNotNull($order);
        $this->assertDatabaseHas('escrows', [
            'order_id' => $order->id,
            'status' => 'locked',
            'amount' => 2000,
        ]);

        $this->actingAs($buyer)
            ->post(route('dashboard.orders.confirm', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('completed', $order->status);

        $seller->wallet->refresh();
        $sellerBalance = (float) $seller->wallet->balance;
        $this->assertGreaterThan(0, $sellerBalance);

        $this->actingAs($buyer)
            ->post(route('dashboard.orders.review', $order), [
                'rating' => 5,
                'comment' => 'Smooth end-to-end purchase.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'listing_id' => $listing->id,
            'rating' => 5,
        ]);

        // Seller withdraws earnings
        $withdrawAmount = min(1000, $sellerBalance);
        $this->actingAs($seller)
            ->post(route('dashboard.withdrawal.store'), [
                'amount' => $withdrawAmount,
                'bank_name' => 'GTBank',
                'account_number' => '0123456789',
                'account_name' => $seller->name,
            ])
            ->assertRedirect(route('dashboard.withdrawal.index'));

        $withdrawal = Withdrawal::where('user_id', $seller->id)->first();
        $this->assertNotNull($withdrawal);

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal))
            ->assertRedirect();

        $this->assertSame('completed', $withdrawal->fresh()->status);

        $this->assertTrue(AuditLog::where('action', 'kyc.approved')->exists());
        $this->assertTrue(AuditLog::where('action', 'funding.approved')->exists());
        $this->assertTrue(AuditLog::where('action', 'listing.approved')->exists());
        $this->assertTrue(AuditLog::where('action', 'withdrawal.approved')->exists());
    }
}
