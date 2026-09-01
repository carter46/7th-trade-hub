<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositBankSubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_bank_wallet_deposit_route_returns_not_found(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(\App\Modules\Wallet\Services\WalletProvisioningService::class)->createWallet($user);

        $this->actingAs($user)
            ->get('/dashboard/deposit/bank')
            ->assertNotFound();

        $this->actingAs($user)
            ->post('/dashboard/deposit/bank', [
                'amount' => 5000,
                'bank_name' => 'GTBank',
                'transfer_reference' => 'TXN-12345',
            ])
            ->assertNotFound();
    }
}
