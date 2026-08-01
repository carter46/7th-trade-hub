<?php

namespace Tests\Feature\Marketplace;

use App\Models\SystemSetting;
use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KycWalletGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_creation_requires_kyc_level_one(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'kyc_level' => 0,
        ]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->post(route('dashboard.wallet.create'))
            ->assertRedirect(route('dashboard.account.kyc'))
            ->assertSessionHas('error');

        $this->assertNull($user->fresh()->wallet);
    }

    public function test_wallet_creation_allowed_when_kyc_not_required(): void
    {
        SystemSetting::set('kyc_required', false);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'kyc_level' => 0,
        ]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->post(route('dashboard.wallet.create'))
            ->assertRedirect(route('dashboard.wallet'))
            ->assertSessionHas('status');

        $this->assertNotNull($user->fresh()->wallet);
    }

    public function test_deposit_auto_provisions_wallet_when_kyc_not_required(): void
    {
        SystemSetting::set('kyc_required', false);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'kyc_level' => 0,
        ]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.deposit.index'))
            ->assertOk();

        $this->assertNotNull($user->fresh()->wallet);
    }

    public function test_wallet_page_offers_create_when_kyc_not_required(): void
    {
        SystemSetting::set('kyc_required', false);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'kyc_level' => 0,
        ]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.wallet'))
            ->assertOk()
            ->assertSee('Create Wallet', false)
            ->assertDontSee('KYC required', false);
    }

    public function test_wallet_creation_succeeds_after_kyc_approval(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->post(route('dashboard.wallet.create'))
            ->assertRedirect(route('dashboard.wallet'))
            ->assertSessionHas('status');

        $this->assertNotNull($user->fresh()->wallet);
    }

    public function test_existing_wallet_is_not_recreated(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);
        $walletId = $user->fresh()->wallet->id;

        $this->actingAs($user)
            ->post(route('dashboard.wallet.create'))
            ->assertRedirect(route('dashboard.wallet'));

        $this->assertSame($walletId, $user->fresh()->wallet->id);
    }
}
