<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoverHubsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_services_hub(): void
    {
        $user = User::factory()->kycApproved()->create(['email_verified_at' => now()]);
        $user->assignRole('user');
        app(WalletProvisioningService::class)->createWallet($user);

        $this->actingAs($user)
            ->get(route('dashboard.services'))
            ->assertOk()
            ->assertSee('Services')
            ->assertSee('My Orders')
            ->assertDontSee('Marketplace');
    }

    public function test_legacy_discover_urls_redirect(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get('/dashboard/discover/marketplace')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get('/dashboard/marketplace')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get('/dashboard/discover/services')
            ->assertRedirect('/dashboard/services');
    }
}
