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

    public function test_admin_cannot_approve_expired_crypto_sell_quote(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $request = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'amount_crypto' => 0.01,
            'quoted_rate_ngn' => 1000000,
            'expected_ngn' => 10000,
            'quoted_at' => now()->subMinutes(20),
            'expires_at' => now()->subMinutes(5),
            'status' => 'pending',
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.crypto-sells.approve', $request), ['tx_hash' => '0xdeadbeef'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('pending', $request->fresh()->status);
        $this->assertDatabaseMissing('wallet_fundings', ['user_id' => $user->id, 'method' => 'crypto']);
    }

    public function test_admin_can_approve_valid_crypto_sell_quote(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $wallet = app(WalletProvisioningService::class)->createWallet($user);

        $request = CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => 'BTC',
            'amount_crypto' => 0.01,
            'quoted_rate_ngn' => 1000000,
            'expected_ngn' => 10000,
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
            'status' => 'pending',
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.crypto-sells.approve', $request), ['tx_hash' => '0xvalidhash'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $wallet->refresh();
        $this->assertEquals(10000.0, (float) $wallet->balance);
        $this->assertSame('approved', $request->fresh()->status);
    }
}
