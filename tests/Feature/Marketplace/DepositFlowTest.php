<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_bank_wallet_deposit_routes_are_removed(): void
    {
        $user = User::factory()->kycApproved()->create();
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);

        $this->actingAs($user)
            ->get('/dashboard/deposit/bank')
            ->assertNotFound();

        $this->actingAs($user)
            ->post('/dashboard/deposit/bank', [
                'amount' => 5000,
                'bank_name' => 'GTBank',
                'transfer_reference' => 'TX123',
            ])
            ->assertNotFound();
    }
}
