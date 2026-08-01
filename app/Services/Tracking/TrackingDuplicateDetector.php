<?php

namespace App\Services\Tracking;

use App\Models\IntegrationProvider;
use App\Models\TrackingScript;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TrackingDuplicateDetector
{
    /**
     * @var array<string, array{label: string, patterns: list<string>}>
     */
    private const SIGNATURES = [
        IntegrationProvider::GOOGLE_TAG_MANAGER => [
            'label' => 'Google Tag Manager',
            'patterns' => [
                'GTM-',
                'googletagmanager.com/gtm.js',
                'googletagmanager.com/ns.html',
            ],
        ],
        IntegrationProvider::GOOGLE_ANALYTICS => [
            'label' => 'Google Analytics',
            'patterns' => [
                'gtag/js?id=G-',
                "gtag('config'",
                'gtag("config"',
                'googletagmanager.com/gtag/js?id=G-',
            ],
        ],
        IntegrationProvider::MICROSOFT_CLARITY => [
            'label' => 'Microsoft Clarity',
            'patterns' => [
                'clarity.ms',
                'window.clarity',
            ],
        ],
        IntegrationProvider::META_PIXEL => [
            'label' => 'Meta Pixel',
            'patterns' => [
                'fbevents.js',
                'fbq(',
                'connect.facebook.net',
            ],
        ],
    ];

    /**
     * @return list<array{
     *     script_id: int|null,
     *     script_name: string,
     *     provider: string,
     *     provider_label: string,
     *     official_enabled: bool,
     *     message: string
     * }>
     */
    public function conflicts(?TrackingScript $focus = null): array
    {
        $conflicts = [];
        $officialEnabled = $this->officialEnabledMap();

        if (($officialEnabled[IntegrationProvider::GOOGLE_TAG_MANAGER] ?? false)
            && ($officialEnabled[IntegrationProvider::GOOGLE_ANALYTICS] ?? false)) {
            $conflicts[] = [
                'script_id' => null,
                'script_name' => 'Official integrations',
                'provider' => IntegrationProvider::GOOGLE_ANALYTICS,
                'provider_label' => 'Google Analytics',
                'official_enabled' => true,
                'message' => __('Google Tag Manager and Google Analytics are both enabled. If your GTM container already loads GA, events may fire twice — prefer GTM alone for GA tags.'),
            ];
        }

        if ($focus === null && ! Schema::hasTable('tracking_scripts')) {
            return $conflicts;
        }

        $scripts = $focus
            ? collect([$focus])->filter(fn ($s) => $s && $s->enabled)
            : TrackingScript::query()->enabled()->orderBy('sort_order')->orderBy('id')->get();

        foreach ($scripts as $script) {
            foreach ($this->detectProviders($script->code.' '.$script->name) as $provider) {
                $label = self::SIGNATURES[$provider]['label'];
                $enabled = $officialEnabled[$provider] ?? false;
                $conflicts[] = [
                    'script_id' => $script->id,
                    'script_name' => $script->name,
                    'provider' => $provider,
                    'provider_label' => $label,
                    'official_enabled' => $enabled,
                    'message' => $enabled
                        ? __('Looks like :provider already exists as an official integration. Running both may duplicate events.', ['provider' => $label])
                        : __('Looks like :provider. Prefer the official integration (ID only) instead of pasting the full snippet.', ['provider' => $label]),
                ];
            }
        }

        return $conflicts;
    }

    /**
     * @return list<string> provider keys
     */
    public function detectProviders(string $haystack): array
    {
        $found = [];
        foreach (self::SIGNATURES as $provider => $meta) {
            foreach ($meta['patterns'] as $pattern) {
                if (stripos($haystack, $pattern) !== false) {
                    $found[] = $provider;
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param  Collection<int, TrackingScript>|null  $scripts
     * @return array<int, list<string>> script id => provider labels
     */
    public function labelsByScriptId(?Collection $scripts = null): array
    {
        if ($scripts === null) {
            if (! Schema::hasTable('tracking_scripts')) {
                return [];
            }
            $scripts = TrackingScript::query()->get();
        }

        $map = [];

        foreach ($scripts as $script) {
            $labels = [];
            foreach ($this->detectProviders($script->code.' '.$script->name) as $provider) {
                $labels[] = self::SIGNATURES[$provider]['label'];
            }
            if ($labels !== []) {
                $map[(int) $script->id] = $labels;
            }
        }

        return $map;
    }

    /**
     * @return array<string, bool>
     */
    private function officialEnabledMap(): array
    {
        $map = [];
        if (! Schema::hasTable('integration_providers')) {
            return $map;
        }

        foreach (array_keys(self::SIGNATURES) as $provider) {
            $row = IntegrationProvider::forProvider($provider);
            $hasCredential = match ($provider) {
                IntegrationProvider::GOOGLE_TAG_MANAGER => filled($row->credential('container_id')),
                IntegrationProvider::GOOGLE_ANALYTICS => filled($row->credential('measurement_id')),
                IntegrationProvider::MICROSOFT_CLARITY => filled($row->credential('project_id')),
                IntegrationProvider::META_PIXEL => filled($row->credential('pixel_id')),
                default => false,
            };
            $map[$provider] = (bool) $row->enabled && $hasCredential;
        }

        return $map;
    }
}
