<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;

class ThemeManager
{
    public const PREFERENCE_LIGHT = 'light';

    public const PREFERENCE_DARK = 'dark';

    public const PREFERENCE_SYSTEM = 'system';

    /**
     * @return list<string>
     */
    public function availablePreferences(): array
    {
        return array_values(config('dashboard-themes.preferences', [
            self::PREFERENCE_LIGHT,
            self::PREFERENCE_DARK,
            self::PREFERENCE_SYSTEM,
        ]));
    }

    /**
     * @return list<string>
     */
    public function availableResolvedThemes(): array
    {
        return array_values(config('dashboard-themes.resolved', [
            self::PREFERENCE_LIGHT,
            self::PREFERENCE_DARK,
        ]));
    }

    public function defaultPreference(): string
    {
        return (string) config('dashboard-themes.default_preference', self::PREFERENCE_SYSTEM);
    }

    public function fallbackTheme(): string
    {
        return (string) config('dashboard-themes.fallback_theme', self::PREFERENCE_LIGHT);
    }

    public function isValidPreference(?string $preference): bool
    {
        return is_string($preference) && in_array($preference, $this->availablePreferences(), true);
    }

    public function isValidResolvedTheme(?string $theme): bool
    {
        return is_string($theme) && in_array($theme, $this->availableResolvedThemes(), true);
    }

    public function preferenceFor(?User $user): string
    {
        $stored = $user?->theme_preference;

        if ($this->isValidPreference($stored)) {
            return $stored;
        }

        return $this->defaultPreference();
    }

    public function resolve(?string $preference, ?string $systemTheme = null): string
    {
        $preference = $this->isValidPreference($preference) ? $preference : $this->defaultPreference();

        if ($preference === self::PREFERENCE_SYSTEM) {
            $system = $this->isValidResolvedTheme($systemTheme) ? $systemTheme : $this->fallbackTheme();

            return $system;
        }

        return $preference;
    }

    public function resolveForUser(?User $user, ?string $systemTheme = null): string
    {
        return $this->resolve($this->preferenceFor($user), $systemTheme);
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(string $theme): array
    {
        $theme = $this->isValidResolvedTheme($theme) ? $theme : $this->fallbackTheme();

        return (array) config('dashboard-themes.themes.'.$theme, []);
    }

    /**
     * @return array<string, string>
     */
    public function tokens(string $theme): array
    {
        return (array) Arr::get($this->definition($theme), 'tokens', []);
    }

    /**
     * @return array<string, string>
     */
    public function charts(string $theme): array
    {
        return (array) Arr::get($this->definition($theme), 'charts', []);
    }

    public function asset(string $key, string $theme): ?string
    {
        $theme = $this->isValidResolvedTheme($theme) ? $theme : $this->fallbackTheme();
        $assets = (array) Arr::get($this->definition($theme), 'assets', []);
        $path = $assets[$key] ?? null;

        if (is_string($path) && $path !== '') {
            return $path;
        }

        $fallback = $assets[$key.'_fallback'] ?? ($assets['logo_fallback'] ?? null);
        if ($key === 'logo' && is_string($fallback) && $fallback !== '') {
            return $fallback;
        }

        $otherTheme = $theme === self::PREFERENCE_LIGHT ? self::PREFERENCE_DARK : self::PREFERENCE_LIGHT;
        $otherAssets = (array) Arr::get($this->definition($otherTheme), 'assets', []);
        $other = $otherAssets[$key] ?? null;

        return is_string($other) && $other !== '' ? $other : null;
    }

    /**
     * @return array<string, string|null>
     */
    public function assetsFor(string $theme): array
    {
        $theme = $this->isValidResolvedTheme($theme) ? $theme : $this->fallbackTheme();
        $assets = (array) Arr::get($this->definition($theme), 'assets', []);

        return [
            'logo' => $this->asset('logo', $theme),
            'empty.default' => $assets['empty.default'] ?? null,
            'empty.wallet' => $assets['empty.wallet'] ?? null,
            'empty.marketplace' => $assets['empty.marketplace'] ?? null,
            'empty.dashboard' => $assets['empty.dashboard'] ?? null,
        ];
    }

    /**
     * Dual light/dark payloads so the client can swap charts/assets when System resolves via matchMedia.
     *
     * @return array{light: array{charts: array<string, string>, assets: array<string, string|null>}, dark: array{charts: array<string, string>, assets: array<string, string|null>}}
     */
    public function themesPayload(): array
    {
        return [
            self::PREFERENCE_LIGHT => [
                'charts' => $this->charts(self::PREFERENCE_LIGHT),
                'assets' => $this->assetsFor(self::PREFERENCE_LIGHT),
            ],
            self::PREFERENCE_DARK => [
                'charts' => $this->charts(self::PREFERENCE_DARK),
                'assets' => $this->assetsFor(self::PREFERENCE_DARK),
            ],
        ];
    }

    /**
     * @return array{preference: string, resolved: string, charts: array<string, string>, assets: array<string, string|null>, themes: array<string, array{charts: array<string, string>, assets: array<string, string|null>}>}
     */
    public function payloadFor(?User $user, ?string $systemTheme = null): array
    {
        $preference = $this->preferenceFor($user);
        $resolved = $this->resolve($preference, $systemTheme);
        $themes = $this->themesPayload();

        return [
            'preference' => $preference,
            'resolved' => $resolved,
            'charts' => $themes[$resolved]['charts'] ?? $this->charts($resolved),
            'assets' => $themes[$resolved]['assets'] ?? $this->assetsFor($resolved),
            'themes' => $themes,
        ];
    }

    /**
     * CSS custom-property map derived from config (paint SSOT).
     *
     * @return array<string, string>
     */
    public function cssVariables(string $theme): array
    {
        $theme = $this->isValidResolvedTheme($theme) ? $theme : $this->fallbackTheme();
        $tokens = $this->tokens($theme);
        $rgb = (array) Arr::get($this->definition($theme), 'rgb', []);
        $charts = $this->charts($theme);
        $vars = [];

        foreach ($tokens as $key => $value) {
            if (is_string($value) && $value !== '') {
                $vars['--th-'.$key] = $value;
            }
        }

        foreach ($rgb as $key => $value) {
            if (is_string($value) && $value !== '') {
                $vars['--th-'.$key.'-rgb'] = $value;
            }
        }

        if (isset($charts['background'])) {
            $vars['--th-chart-bg'] = (string) $charts['background'];
        }
        if (isset($charts['grid'])) {
            $vars['--th-chart-grid'] = (string) $charts['grid'];
        }
        if (isset($charts['labels'])) {
            $vars['--th-chart-labels'] = (string) $charts['labels'];
        }

        return $vars;
    }

    /**
     * Render a CSS ruleset for a selector from config tokens.
     */
    public function cssRuleset(string $selector, string $theme, array $extraDeclarations = []): string
    {
        $lines = [];
        foreach ($this->cssVariables($theme) as $property => $value) {
            $lines[] = $property.': '.$value.';';
        }
        foreach ($extraDeclarations as $property => $value) {
            $lines[] = $property.': '.$value.';';
        }

        return $selector." {\n    ".implode("\n    ", $lines)."\n}";
    }

    /**
     * Full dashboard theme stylesheet (config → CSS). Marketing keeps its own palette in app.css.
     */
    public function dashboardThemeStylesheet(): string
    {
        $blocks = [
            $this->cssRuleset("html[data-theme='light'], .dashboard-shell[data-theme='light']", self::PREFERENCE_LIGHT, [
                'color-scheme' => 'light',
            ]),
            $this->cssRuleset("html[data-theme='dark'], .dashboard-shell[data-theme='dark']", self::PREFERENCE_DARK, [
                'color-scheme' => 'dark',
            ]),
        ];

        return implode("\n\n", $blocks);
    }
}
