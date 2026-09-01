<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletManualBankRetiredTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_index_has_no_manual_bank_cta(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(\App\Modules\Wallet\Services\WalletProvisioningService::class)->createWallet($user);

        $this->actingAs($user)
            ->get(route('dashboard.deposit.index'))
            ->assertOk()
            ->assertDontSee('Manual bank')
            ->assertDontSee('create-bank');
    }
}
