<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\Withdrawal;
use App\Modules\Wallet\Payments\PayoutGateway;
use App\Modules\Wallet\Services\SecurityVerificationService;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\FakePaymentRail;
use Tests\TestCase;

class WithdrawalMonnifyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        FakePaymentRail::reset();
        $this->app->bind(
            \App\Modules\Wallet\Payments\Contracts\PaymentRailInterface::class,
            FakePaymentRail::class
        );
        $this->app->bind(
            PayoutGateway::class,
            fn ($app) => PayoutGateway::from($app->make(\App\Modules\Wallet\Payments\Contracts\PaymentRailInterface::class))
        );
    }

    private function createPendingWithdrawal(User $user): Withdrawal
    {
        app(WalletProvisioningService::class)->createWallet($user);
        app(WalletService::class)->adminAdjust($user->fresh()->wallet, 5000, 'Test', 1);

        $bank = UserBankAccount::create([
            'user_id' => $user->id,
            'bank_name' => 'GTBank',
            'bank_code' => '058',
            'account_number' => '0123456789',
            'verified_name' => $user->name,
            'verified_at' => now(),
            'verified_by' => 'monnify',
            'active' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'wallet_id' => $user->fresh()->wallet->id,
            'user_bank_account_id' => $bank->id,
            'amount' => 1500,
            'currency' => 'NGN',
            'bank_name' => $bank->bank_name,
            'bank_code' => $bank->bank_code,
            'account_number' => $bank->account_number,
            'account_name' => $bank->verified_name,
            'status' => 'pending',
            'internal_status' => 'pending_review',
            'reference' => 'WDR-TESTAUTH01',
        ]);

        app(WalletService::class)->lockForWithdrawal($withdrawal);

        return $withdrawal;
    }

    public function test_approve_pending_authorization_then_admin_authorizes_otp(): void
    {
        FakePaymentRail::$initiateResult = [
            'status' => 'PENDING_AUTHORIZATION',
            'totalFee' => 35,
            'destinationBankName' => 'GTBank',
            'destinationAccountNumber' => '0123456789',
        ];
        FakePaymentRail::$authorizeResult = ['status' => 'SUCCESS'];
        FakePaymentRail::$transferStatus = ['status' => 'SUCCESS'];

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $withdrawal = $this->createPendingWithdrawal($user);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal), ['confirm_send' => '1'])
            ->assertRedirect(route('admin.withdrawals.show', $withdrawal));

        $withdrawal->refresh();
        $this->assertTrue($withdrawal->needsProviderAuthorization());
        $this->assertSame(Withdrawal::INTERNAL_AWAITING_PROVIDER_AUTH, $withdrawal->internal_status);
        $this->assertNotEmpty($withdrawal->provider_meta['initiate'] ?? null);

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.authorize-provider', $withdrawal), [
                'authorization_code' => '990501',
            ])
            ->assertRedirect(route('admin.withdrawals.show', $withdrawal));

        $withdrawal->refresh();
        $this->assertSame('completed', $withdrawal->status);
        $this->assertSame('990501', FakePaymentRail::$lastAuthorizeCode);
    }

    public function test_expired_provider_status_blocks_authorization(): void
    {
        FakePaymentRail::$initiateResult = ['status' => 'PENDING_AUTHORIZATION'];
        FakePaymentRail::$transferStatus = ['status' => 'EXPIRED'];

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $withdrawal = $this->createPendingWithdrawal($user);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal), ['confirm_send' => '1']);

        $withdrawal->refresh();
        $withdrawal->update(['provider_status' => 'EXPIRED']);

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.authorize-provider', $withdrawal), [
                'authorization_code' => '990501',
            ])
            ->assertRedirect(route('admin.withdrawals.show', $withdrawal))
            ->assertSessionHas('error');
    }

    public function test_invalid_otp_increments_attempts(): void
    {
        FakePaymentRail::$initiateResult = ['status' => 'PENDING_AUTHORIZATION'];
        FakePaymentRail::$transferStatus = ['status' => 'PENDING_AUTHORIZATION'];

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $withdrawal = $this->createPendingWithdrawal($user);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal), ['confirm_send' => '1']);

        $withdrawal->refresh();

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.authorize-provider', $withdrawal), [
                'authorization_code' => '000000',
            ])
            ->assertSessionHas('error');

        $this->assertSame(1, (int) $withdrawal->fresh()->provider_auth_attempts);
    }

    public function test_admin_can_reject_while_awaiting_provider_authorization(): void
    {
        FakePaymentRail::$initiateResult = ['status' => 'PENDING_AUTHORIZATION'];

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $withdrawal = $this->createPendingWithdrawal($user);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal), ['confirm_send' => '1']);

        $withdrawal->refresh();
        $this->assertTrue($withdrawal->needsProviderAuthorization());

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.reject', $withdrawal))
            ->assertSessionHas('status');

        $withdrawal->refresh();
        $this->assertSame('rejected', $withdrawal->status);
    }

    public function test_show_page_marks_expired_authorization_as_failed(): void
    {
        FakePaymentRail::$initiateResult = ['status' => 'PENDING_AUTHORIZATION'];
        FakePaymentRail::$transferStatus = ['status' => 'EXPIRED'];

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        $withdrawal = $this->createPendingWithdrawal($user);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.withdrawals.approve', $withdrawal), ['confirm_send' => '1']);

        $this->actingAs($admin)
            ->get(route('admin.withdrawals.show', $withdrawal))
            ->assertOk();

        $withdrawal->refresh();
        $this->assertSame('failed', $withdrawal->status);
        $this->assertSame('EXPIRED', $withdrawal->provider_status);
    }
}
