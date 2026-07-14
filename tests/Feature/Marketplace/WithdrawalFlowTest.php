<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Withdrawal;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_withdrawal_request_locks_balance_and_admin_approval_completes_payout(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);
        $user->refresh();

        app(WalletService::class)->adminAdjust($user->wallet, 10000, 'Test credit', 1);

        $this->actingAs($user)
            ->post(route('dashboard.withdrawal.store'), [
                'amount' => 3000,
                'bank_name' => 'GTBank',
                'account_number' => '0123456789',
                'account_name' => 'Test User',
            ])
            ->assertRedirect(route('dashboard.withdrawal.index'));

        $user->wallet->refresh();
        $this->assertEquals(7000.0, (float) $user->wallet->balance);
        $this->assertEquals(3000.0, (float) $user->wallet->locked_balance);

        $withdrawal = Withdrawal::where('user_id', $user->id)->first();
        $this->assertNotNull($withdrawal);
        $this->assertSame('pending', $withdrawal->status);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal))
            ->assertRedirect();

        $user->wallet->refresh();
        $withdrawal->refresh();

        $this->assertSame('completed', $withdrawal->status);
        $this->assertEquals(7000.0, (float) $user->wallet->balance);
        $this->assertEquals(0.0, (float) $user->wallet->locked_balance);
    }
}
