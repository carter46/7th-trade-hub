<?php

namespace App\Services\Tracking;

use App\Models\IntegrationProvider;
use App\Models\SystemSetting;
use App\Models\TrackingScript;
use App\Services\Analytics\Providers\GoogleAnalyticsProvider;
use App\Services\Analytics\Providers\MicrosoftClarityProvider;
use App\Services\Communications\LiveChat\LiveChatManager;
use App\Services\Tracking\Providers\GoogleTagManagerProvider;
use App\Services\Tracking\Providers\MetaPixelProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class TrackingScriptRenderer
{
    public const CACHE_KEY = 'tracking.compiled_v1';

    public function __construct(
        private GoogleTagManagerProvider $gtm,
        private GoogleAnalyticsProvider $ga,
        private MetaPixelProvider $meta,
        private MicrosoftClarityProvider $clarity,
        private LiveChatManager $liveChat,
    ) {}

    public function headHtml(): string
    {
        return $this->compiled()['head'];
    }

    public function bodyStartHtml(): string
    {
        return $this->compiled()['body_start'];
    }

    public function bodyEndHtml(): string
    {
        return $this->compiled()['body_end'];
    }

    /**
     * @return array{
     *   head: list<array{label: string, source: string, location: string, enabled: bool}>,
     *   body_start: list<array{label: string, source: string, location: string, enabled: bool}>,
     *   body_end: list<array{label: string, source: string, location: string, enabled: bool}>,
     *   html: array{head: string, body_start: string, body_end: string}
     * }
     */
    public function itemsForPreview(): array
    {
        $parts = $this->buildParts(includeDisabledCustom: false);

        return [
            'head' => $parts['inventory']['head'],
            'body_start' => $parts['inventory']['body_start'],
            'body_end' => $parts['inventory']['body_end'],
            'html' => [
                'head' => $parts['html']['head'],
                'body_start' => $parts['html']['body_start'],
                'body_end' => $parts['html']['body_end'],
            ],
        ];
    }

    public function flushCache(): void
    {
        $stamp = Cache::get(self::CACHE_KEY.'.stamp');
        if (is_string($stamp) && $stamp !== '') {
            Cache::forget(self::CACHE_KEY.':'.$stamp);
        }
        Cache::forget(self::CACHE_KEY.'.stamp');
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{head: string, body_start: string, body_end: string}
     */
    private function compiled(): array
    {
        $stamp = $this->contentStamp();
        Cache::put(self::CACHE_KEY.'.stamp', $stamp, now()->addDay());

        return Cache::remember(self::CACHE_KEY.':'.$stamp, now()->addDay(), function () {
            $parts = $this->buildParts(includeDisabledCustom: false);

            return $parts['html'];
        });
    }

    private function contentStamp(): string
    {
        $parts = [];

        if (Schema::hasTable('integration_providers')) {
            $parts[] = (string) IntegrationProvider::query()
                ->whereIn('provider', [
                    IntegrationProvider::GOOGLE_TAG_MANAGER,
                    IntegrationProvider::GOOGLE_ANALYTICS,
                    IntegrationProvider::MICROSOFT_CLARITY,
                    IntegrationProvider::META_PIXEL,
                ])
                ->max('updated_at');
        }

        if (Schema::hasTable('tracking_scripts')) {
            $parts[] = (string) TrackingScript::query()->max('updated_at');
            $parts[] = (string) TrackingScript::query()->count();
        }

        if (Schema::hasTable('system_settings')) {
            $parts[] = (string) SystemSetting::query()
                ->whereIn('key', [
                    'verification_google',
                    'verification_bing',
                    'verification_facebook',
                ])
                ->max('updated_at');
        }

        return md5(implode('|', $parts));
    }

    /**
     * @return array{
     *   html: array{head: string, body_start: string, body_end: string},
     *   inventory: array{
     *     head: list<array{label: string, source: string, location: string, enabled: bool}>,
     *     body_start: list<array{label: string, source: string, location: string, enabled: bool}>,
     *     body_end: list<array{label: string, source: string, location: string, enabled: bool}>
     *   }
     * }
     */
    private function buildParts(bool $includeDisabledCustom): array
    {
        $inventory = [
            'head' => [],
            'body_start' => [],
            'body_end' => [],
        ];
        $headChunks = [];
        $bodyStartChunks = [];
        $bodyEndChunks = [];

        foreach ($this->verificationTags() as $tag) {
            $inventory['head'][] = [
                'label' => $tag['label'],
                'source' => 'verification',
                'location' => TrackingScript::LOCATION_HEAD,
                'enabled' => true,
            ];
            $headChunks[] = $tag['html'];
        }

        if ($this->gtm->isEnabled()) {
            $html = $this->gtm->headScript();
            if ($html) {
                $inventory['head'][] = [
                    'label' => 'Google Tag Manager',
                    'source' => 'official:google_tag_manager',
                    'location' => TrackingScript::LOCATION_HEAD,
                    'enabled' => true,
                ];
                $headChunks[] = $html;
            }
            $noscript = $this->gtm->bodyNoscript();
            if ($noscript) {
                $inventory['body_start'][] = [
                    'label' => 'Google Tag Manager (noscript)',
                    'source' => 'official:google_tag_manager',
                    'location' => TrackingScript::LOCATION_BODY_START,
                    'enabled' => true,
                ];
                $bodyStartChunks[] = $noscript;
            }
        }

        if ($this->ga->isEnabled()) {
            $html = $this->ga->measurementScript();
            if ($html) {
                $inventory['head'][] = [
                    'label' => 'Google Analytics',
                    'source' => 'official:google_analytics',
                    'location' => TrackingScript::LOCATION_HEAD,
                    'enabled' => true,
                ];
                $headChunks[] = $html;
            }
        }

        if ($this->meta->isEnabled()) {
            $html = $this->meta->headScript();
            if ($html) {
                $inventory['head'][] = [
                    'label' => 'Meta Pixel',
                    'source' => 'official:meta_pixel',
                    'location' => TrackingScript::LOCATION_HEAD,
                    'enabled' => true,
                ];
                $headChunks[] = $html;
            }
        }

        if ($this->clarity->isEnabled()) {
            $html = $this->clarity->script();
            if ($html) {
                $inventory['head'][] = [
                    'label' => 'Microsoft Clarity',
                    'source' => 'official:microsoft_clarity',
                    'location' => TrackingScript::LOCATION_HEAD,
                    'enabled' => true,
                ];
                $headChunks[] = $html;
            }
        }

        // Preview mirrors layout order: live chat (marketing only) then custom body_end.
        $chat = $this->liveChat->resolved();
        $chatEnabled = (bool) ($chat['enabled'] ?? false) && ($chat['provider'] ?? 'none') !== 'none';
        $inventory['body_end'][] = [
            'label' => $chatEnabled
                ? 'Live chat widget ('.$chat['label'].', marketing pages only)'
                : 'Live chat widget (disabled — Settings → Contact)',
            'source' => 'system:live_chat',
            'location' => TrackingScript::LOCATION_BODY_END,
            'enabled' => $chatEnabled,
        ];

        if (Schema::hasTable('tracking_scripts')) {
            $query = TrackingScript::query()->orderBy('sort_order')->orderBy('id');
            if (! $includeDisabledCustom) {
                $query->enabled();
            }

            foreach ($query->get() as $script) {
                if (! $script->enabled && ! $includeDisabledCustom) {
                    continue;
                }

                $item = [
                    'label' => 'Custom: '.$script->name,
                    'source' => 'custom:'.$script->id,
                    'location' => $script->location,
                    'enabled' => (bool) $script->enabled,
                ];

                if (! $script->enabled) {
                    continue;
                }

                $code = $this->sanitizeCustomCode((string) $script->code);
                if ($code === '') {
                    continue;
                }

                if ($script->location === TrackingScript::LOCATION_BODY_START) {
                    $inventory['body_start'][] = $item;
                    $bodyStartChunks[] = $code;
                } elseif ($script->location === TrackingScript::LOCATION_BODY_END) {
                    $inventory['body_end'][] = $item;
                    $bodyEndChunks[] = $code;
                } else {
                    $inventory['head'][] = $item;
                    $headChunks[] = $code;
                }
            }
        }

        return [
            'html' => [
                'head' => $this->join($headChunks),
                'body_start' => $this->join($bodyStartChunks),
                'body_end' => $this->join($bodyEndChunks),
            ],
            'inventory' => $inventory,
        ];
    }

    /**
     * @return list<array{label: string, html: string}>
     */
    private function verificationTags(): array
    {
        if (! Schema::hasTable('system_settings')) {
            return [];
        }

        $tags = [];

        $google = trim((string) SystemSetting::get('verification_google', ''));
        if ($google !== '') {
            $tags[] = [
                'label' => 'Google site verification',
                'html' => '<meta name="google-site-verification" content="'.e($google).'">',
            ];
        }

        $bing = trim((string) SystemSetting::get('verification_bing', ''));
        if ($bing !== '') {
            $tags[] = [
                'label' => 'Bing webmaster verification',
                'html' => '<meta name="msvalidate.01" content="'.e($bing).'">',
            ];
        }

        $facebook = trim((string) SystemSetting::get('verification_facebook', ''));
        if ($facebook !== '') {
            $tags[] = [
                'label' => 'Facebook domain verification',
                'html' => '<meta name="facebook-domain-verification" content="'.e($facebook).'">',
            ];
        }

        return $tags;
    }

    private function sanitizeCustomCode(string $code): string
    {
        $trimmed = trim($code);
        if ($trimmed === '') {
            return '';
        }

        if (preg_match('/<\?(php|=)?/i', $trimmed) || stripos($trimmed, '<%') !== false) {
            return '';
        }

        return $trimmed;
    }

    /**
     * @param  list<string>  $chunks
     */
    private function join(array $chunks): string
    {
        return collect($chunks)
            ->filter(fn ($chunk) => filled($chunk))
            ->implode("\n");
    }
}
