<?php

namespace Tests\Feature\Admin;

use App\Models\CryptoSellRequest;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CryptoSellRejectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reject_pending_crypto_sell(): void
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
            ->post(route('admin.crypto-sells.reject', $request), ['notes' => 'Invalid proof'])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame('rejected', $request->status);
        $this->assertSame('Invalid proof', $request->admin_notes);
        $this->assertDatabaseHas('audit_logs', ['action' => 'crypto_sell.rejected']);
    }
}
