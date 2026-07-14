<?php

namespace Tests\Feature\Marketplace;

use App\Models\Listing;
use App\Models\User;
use App\Models\Wallet;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_deposit_creates_pending_wallet_funding(): void
    {
        $user = User::factory()->kycApproved()->create();
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);

        $user->refresh();
        $this->assertNotNull($user->wallet, 'Wallet must exist before deposit');

        $response = $this->actingAs($user)
            ->from(route('dashboard.deposit.create-bank'))
            ->post(route('dashboard.deposit.store-bank'), [
                'amount' => 5000,
                'bank_name' => 'GTBank',
                'transfer_reference' => 'TX123',
            ]);

        $response->assertRedirect(route('dashboard.deposit.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('wallet_fundings', [
            'user_id' => $user->id,
            'method' => 'bank',
            'status' => 'pending',
        ]);
    }
}
