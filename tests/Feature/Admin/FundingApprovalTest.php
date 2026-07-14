<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\WalletFunding;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundingApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_approve_bank_deposit(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);
        $user->refresh();

        $funding = WalletFunding::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'method' => 'bank',
            'amount' => 5000,
            'currency' => 'NGN',
            'status' => 'pending',
            'reference' => 'DEP-TEST-001',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.fundings.approve', $funding))
            ->assertRedirect();

        $user->wallet->refresh();
        $funding->refresh();

        $this->assertSame('approved', $funding->status);
        $this->assertEquals(5000.0, (float) $user->wallet->balance);
        $this->assertDatabaseHas('audit_logs', ['action' => 'funding.approved']);
    }

    public function test_admin_can_reverse_approved_deposit(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);
        $user->refresh();

        $funding = WalletFunding::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'method' => 'bank',
            'amount' => 3000,
            'currency' => 'NGN',
            'status' => 'pending',
            'reference' => 'DEP-TEST-002',
        ]);

        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.fundings.approve', $funding));
        $user->wallet->refresh();
        $this->assertEquals(3000.0, (float) $user->wallet->balance);

        $this->actingAs($admin)
            ->post(route('admin.fundings.reverse', $funding->fresh()), ['reason' => 'Duplicate deposit'])
            ->assertRedirect();

        $user->wallet->refresh();
        $funding->refresh();

        $this->assertEquals(0.0, (float) $user->wallet->balance);
        $this->assertSame('reversed', $funding->status);
        $this->assertTrue(AuditLog::where('action', 'funding.reversed')->exists());
    }
}
