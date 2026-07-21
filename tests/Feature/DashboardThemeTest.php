<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_theme_switcher_and_data_theme(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme=', false)
            ->assertSee('data-theme-preference=', false)
            ->assertSee('themeSwitcher', false)
            ->assertSee('dashboard-shell', false)
            ->assertSee('h-dvh', false)
            ->assertSee('--th-surface:', false);
    }

    public function test_admin_shell_is_scrollable_viewport(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin'))
            ->assertOk()
            ->assertSee('h-dvh', false)
            ->assertSee('overflow-y-auto', false)
            ->assertSee('min-h-0', false)
            ->assertSee('themeSwitcher', false)
            ->assertDontSee('overflow-x-hidden', false);
    }

    public function test_marketing_home_has_no_dashboard_theme_switcher(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('themeSwitcher', false)
            ->assertDontSee('dashboard-shell', false);
    }

    public function test_theme_preference_can_be_saved_via_json(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->putJson(route('theme.preference'), ['theme' => 'dark'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('preference', 'dark')
            ->assertJsonPath('resolved', 'dark')
            ->assertJsonStructure(['themes' => ['light', 'dark']]);

        $this->assertSame('dark', $user->fresh()->theme_preference);
    }

    public function test_system_preference_save_returns_dual_theme_payload(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $response = $this->actingAs($user)
            ->putJson(route('theme.preference'), [
                'theme' => 'system',
                'system_theme' => 'dark',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('preference', 'system')
            ->assertJsonPath('resolved', 'dark')
            ->assertJsonStructure([
                'themes' => [
                    'light' => ['charts', 'assets'],
                    'dark' => ['charts', 'assets'],
                ],
            ]);

        $this->assertSame('system', $user->fresh()->theme_preference);
        $this->assertArrayHasKey('logo', $response->json('themes.dark.assets'));
    }

    public function test_system_without_hint_falls_back_to_light_resolved_but_keeps_preference(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->putJson(route('theme.preference'), ['theme' => 'system'])
            ->assertOk()
            ->assertJsonPath('preference', 'system')
            ->assertJsonPath('resolved', 'light')
            ->assertJsonStructure(['themes' => ['light', 'dark']]);

        $this->assertSame('system', $user->fresh()->theme_preference);
    }

    public function test_theme_preference_rejects_invalid_values(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->putJson(route('theme.preference'), ['theme' => 'neon'])
            ->assertStatus(422);
    }

    public function test_guest_cannot_update_theme_preference(): void
    {
        $this->putJson(route('theme.preference'), ['theme' => 'dark'])
            ->assertUnauthorized();
    }

    public function test_saved_preference_is_rendered_on_dashboard(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'theme_preference' => 'dark',
        ]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme="dark"', false)
            ->assertSee('data-theme-preference="dark"', false);
    }

    public function test_modal_component_uses_theme_tokens(): void
    {
        $html = (string) $this->view('components.ui.modal', [
            'name' => 'test-modal',
            'title' => 'Confirm',
        ]);

        $this->assertStringContainsString('bg-elevated', $html);
        $this->assertStringContainsString('border-border-default', $html);
        $this->assertStringContainsString('bg-overlay', $html);
        $this->assertStringContainsString('text-text-primary', $html);
        $this->assertStringNotContainsString('bg-white', $html);
        $this->assertStringNotContainsString('border-slate-200', $html);
    }

    public function test_sql_dump_includes_theme_preference_column(): void
    {
        $sql = file_get_contents(database_path('sql/migration.sql'));

        $this->assertNotFalse($sql);
        $this->assertStringContainsString('theme_preference', $sql);
        $this->assertMatchesRegularExpression(
            '/CREATE TABLE IF NOT EXISTS `users`[\s\S]*`theme_preference` varchar\(16\)/',
            $sql
        );
    }
}
