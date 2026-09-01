<?php

namespace Tests\Feature\Wallet;

use App\Models\AdminNotification;
use App\Models\User;
use App\Models\WalletFunding;
use App\Modules\Wallet\Services\WalletProvisioningService;
use App\Services\Communications\Email\EmailService;
use App\Services\Communications\Email\SendResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DepositBankSubmitTest extends TestCase
{
    use RefreshDatabase;

    private function financeAdmin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('finance.manage');

        return $admin;
    }

    public function test_manual_bank_deposit_submission_notifies_admins(): void
    {
        $this->financeAdmin();

        $mock = Mockery::mock(EmailService::class);
        $mock->shouldReceive('sendMailableHtml')->andReturn(SendResult::ok('test', 'msg-1'));
        $this->app->instance(EmailService::class, $mock);

        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);
        $user->refresh();

        $this->actingAs($user)
            ->post(route('dashboard.deposit.store-bank'), [
                'amount' => 5000,
                'bank_name' => 'GTBank',
                'transfer_reference' => 'TXN-12345',
            ])
            ->assertRedirect(route('dashboard.deposit.index'));

        $this->assertDatabaseHas('wallet_fundings', [
            'user_id' => $user->id,
            'method' => 'bank',
            'amount' => 5000,
        ]);

        $funding = WalletFunding::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($funding);

        $this->assertDatabaseHas('admin_notifications', [
            'type' => 'wallet.deposit_submitted',
        ]);

        $notification = AdminNotification::query()
            ->where('type', 'wallet.deposit_submitted')
            ->first();

        $this->assertSame('wallet.deposit_submitted.'.$funding->id, $notification->meta['dedupe_key'] ?? null);
    }
}
