<?php

namespace Tests\Unit;

use App\Services\ThemeManager;
use Tests\TestCase;

class ThemeManagerTest extends TestCase
{
    public function test_available_preferences_include_system(): void
    {
        $manager = app(ThemeManager::class);

        $this->assertSame(['light', 'dark', 'system'], $manager->availablePreferences());
        $this->assertSame(['light', 'dark'], $manager->availableResolvedThemes());
    }

    public function test_system_preference_resolves_from_os_hint(): void
    {
        $manager = app(ThemeManager::class);

        $this->assertSame('dark', $manager->resolve('system', 'dark'));
        $this->assertSame('light', $manager->resolve('system', 'light'));
        $this->assertSame('light', $manager->resolve('system', null));
    }

    public function test_explicit_preferences_win(): void
    {
        $manager = app(ThemeManager::class);

        $this->assertSame('dark', $manager->resolve('dark', 'light'));
        $this->assertSame('light', $manager->resolve('light', 'dark'));
    }

    public function test_tokens_and_assets_exist_for_both_themes(): void
    {
        $manager = app(ThemeManager::class);

        foreach (['light', 'dark'] as $theme) {
            $tokens = $manager->tokens($theme);
            $this->assertArrayHasKey('surface', $tokens);
            $this->assertArrayHasKey('text-primary', $tokens);
            $this->assertArrayHasKey('sidebar', $tokens);
            $this->assertNotEmpty($manager->charts($theme));
            $this->assertNotNull($manager->asset('logo', $theme));
        }
    }

    public function test_payload_includes_dual_themes(): void
    {
        $manager = app(ThemeManager::class);
        $payload = $manager->payloadFor(null, 'dark');

        $this->assertSame('system', $payload['preference']);
        $this->assertSame('dark', $payload['resolved']);
        $this->assertArrayHasKey('light', $payload['themes']);
        $this->assertArrayHasKey('dark', $payload['themes']);
        $this->assertArrayHasKey('charts', $payload['themes']['dark']);
        $this->assertArrayHasKey('assets', $payload['themes']['light']);
    }

    public function test_css_stylesheet_is_generated_from_config_tokens(): void
    {
        $manager = app(ThemeManager::class);
        $css = $manager->dashboardThemeStylesheet();
        $lightSurface = $manager->tokens('light')['surface'];
        $darkSurface = $manager->tokens('dark')['surface'];

        $this->assertStringContainsString("html[data-theme='light']", $css);
        $this->assertStringContainsString("html[data-theme='dark']", $css);
        $this->assertStringContainsString('--th-surface: '.$lightSurface, $css);
        $this->assertStringContainsString('--th-surface: '.$darkSurface, $css);
        $this->assertStringContainsString('color-scheme: light', $css);
        $this->assertStringContainsString('color-scheme: dark', $css);
    }
}
