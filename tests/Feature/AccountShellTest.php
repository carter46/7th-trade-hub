<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_account_profile_uses_admin_shell(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.account.profile'))
            ->assertOk()
            ->assertSee('data-dashboard-nav="admin"', false)
            ->assertSee('My Account')
            ->assertSee('data-account-menu', false);
    }

    public function test_user_account_profile_uses_user_shell(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard.account.profile'))
            ->assertOk()
            ->assertSee('data-dashboard-nav="user"', false)
            ->assertSee('My Account');
    }

    public function test_legacy_profile_redirects_admin_to_admin_account(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->get(route('profile.edit'))
            ->assertRedirect(route('admin.account.profile'));
    }

    public function test_legacy_profile_redirects_user_to_dashboard_account(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('dashboard.account.profile'));
    }
}
