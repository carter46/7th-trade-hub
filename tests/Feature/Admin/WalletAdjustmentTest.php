<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_credit_user_wallet(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);
        $user->refresh();

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.wallet-adjustment.store'), [
                'user_email' => $user->email,
                'amount' => 2500,
                'reason' => 'Promotional credit',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertEquals(2500.0, (float) $user->wallet->fresh()->balance);
        $this->assertDatabaseHas('audit_logs', ['action' => 'wallet.adjusted']);
    }
}
