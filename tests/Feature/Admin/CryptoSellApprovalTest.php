<?php

namespace Tests\Feature\Admin;

use App\Models\CryptoSellRequest;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CryptoSellApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_approve_expired_waiting_deposit_without_checklist(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $request = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 500,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 700000,
            'quoted_at' => now()->subMinutes(20),
            'expires_at' => now()->subMinutes(5),
            'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        // Quote expiry no longer blocks approve for matched verifying flow,
        // but checklist is required — without it validation fails.
        $this->actingAs($admin)
            ->post(route('admin.crypto-sells.approve', $request), ['tx_hash' => '0xdeadbeef'])
            ->assertSessionHasErrors();

        $this->assertSame(CryptoSellRequest::STATUS_WAITING_DEPOSIT, $request->fresh()->status);
        $this->assertDatabaseMissing('wallet_fundings', ['user_id' => $user->id, 'method' => 'crypto']);
    }

    public function test_admin_can_approve_with_checklist_and_credits_locked_quote(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $request = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'network' => 'bitcoin',
            'amount_crypto' => 0.01,
            'amount_usd' => 100,
            'quoted_rate_ngn' => 1400,
            'market_rate_ngn' => 1425,
            'spread_ngn' => 25,
            'expected_ngn' => 140000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_VERIFYING,
            'tx_hash' => '0xvalidhash',
            'platform_address' => 'bc1qtest',
            'required_confirmations' => 2,
            'confirmations_observed' => 2,
            'amount_match_status' => 'exact',
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.crypto-sells.approve', $request), [
                'tx_hash' => '0xvalidhash',
                'checklist_network' => '1',
                'checklist_destination' => '1',
                'checklist_amount' => '1',
                'checklist_confirmations' => '1',
                'checklist_valid' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $wallet->refresh();
        $this->assertEquals(140000.0, (float) $wallet->balance);
        $this->assertSame(CryptoSellRequest::STATUS_APPROVED, $request->fresh()->status);
    }

    public function test_duplicate_tx_hash_is_rejected(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'amount_crypto' => 0.01,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 14000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_APPROVED,
            'tx_hash' => 'abc123unique',
        ]);

        $request = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'amount_crypto' => 0.02,
            'quoted_rate_ngn' => 1400,
            'expected_ngn' => 28000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => CryptoSellRequest::STATUS_VERIFYING,
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.crypto-sells.approve', $request), [
                'tx_hash' => 'abc123unique',
                'checklist_network' => '1',
                'checklist_destination' => '1',
                'checklist_amount' => '1',
                'checklist_confirmations' => '1',
                'checklist_valid' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(CryptoSellRequest::STATUS_VERIFYING, $request->fresh()->status);
    }
}
