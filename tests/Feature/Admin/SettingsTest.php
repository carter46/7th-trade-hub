<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_platform_settings(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'platform_fee_percent' => 3,
                'withdrawal_min_amount' => 500,
                'withdrawal_max_amount' => 500000,
                'deposit_min_amount' => 200,
            ])
            ->assertRedirect();

        $this->assertSame('3', SystemSetting::get('platform_fee_percent'));
        $this->assertSame('500', SystemSetting::get('withdrawal_min_amount'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.updated']);
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('admin.settings'))
            ->assertForbidden();
    }
}
