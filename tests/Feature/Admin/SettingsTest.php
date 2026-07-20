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
                'live_chat_provider' => 'none',
                'smartsupp_key' => '',
                'jivo_widget_id' => '',
                'contact_phone' => '+234 800 000 0000',
                'contact_email' => 'support@example.com',
                'contact_email_alt' => '',
            ])
            ->assertRedirect();

        $this->assertSame('3', SystemSetting::get('platform_fee_percent'));
        $this->assertSame('500', SystemSetting::get('withdrawal_min_amount'));
        $this->assertSame('none', SystemSetting::get('live_chat_provider'));
        $this->assertSame('support@example.com', SystemSetting::get('contact_email'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.updated']);
    }

    public function test_admin_can_save_smartsupp_key(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'platform_fee_percent' => 2.5,
                'withdrawal_min_amount' => 100,
                'withdrawal_max_amount' => 1000000,
                'deposit_min_amount' => 100,
                'live_chat_provider' => 'smartsupp',
                'smartsupp_key' => 'test-smartsupp-key-123',
                'jivo_widget_id' => '',
                'contact_phone' => '',
                'contact_email' => 'hello@example.com',
                'contact_email_alt' => '',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('smartsupp', SystemSetting::get('live_chat_provider'));
        $this->assertSame('test-smartsupp-key-123', SystemSetting::get('smartsupp_key'));
    }

    public function test_smartsupp_requires_key(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'platform_fee_percent' => 2.5,
                'withdrawal_min_amount' => 100,
                'withdrawal_max_amount' => 1000000,
                'deposit_min_amount' => 100,
                'live_chat_provider' => 'smartsupp',
                'smartsupp_key' => '',
                'jivo_widget_id' => '',
                'contact_phone' => '',
                'contact_email' => '',
                'contact_email_alt' => '',
            ])
            ->assertSessionHasErrors('smartsupp_key');
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
