<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\UserBankAccount;
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

        $bank = UserBankAccount::create([
            'user_id' => $user->id,
            'bank_name' => 'GTBank',
            'bank_code' => '058',
            'account_number' => '0123456789',
            'verified_name' => 'Test User',
            'verified_at' => now(),
            'verified_by' => 'monnify',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.withdrawal.store'), [
                'amount' => 3000,
                'user_bank_account_id' => $bank->id,
            ])
            ->assertRedirect();

        $user->wallet->refresh();
        $this->assertEquals(10000.0, (float) $user->wallet->balance);
        $this->assertEquals(3000.0, (float) $user->wallet->locked_balance);
        $this->assertEquals(7000.0, $user->wallet->availableBalance());

        $withdrawal = Withdrawal::where('user_id', $user->id)->first();
        $this->assertNotNull($withdrawal);
        $this->assertSame('pending', $withdrawal->status);
        $this->assertSame('GTBank', $withdrawal->bank_name);
        $this->assertSame('0123456789', $withdrawal->account_number);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal), [
                'confirm_send' => '1',
            ])
            ->assertRedirect();

        $user->wallet->refresh();
        $withdrawal->refresh();

        $this->assertSame('completed', $withdrawal->status);
        $this->assertEquals(7000.0, (float) $user->wallet->balance);
        $this->assertEquals(0.0, (float) $user->wallet->locked_balance);
    }
}
