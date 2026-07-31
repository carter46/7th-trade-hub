<?php

namespace App\Services\Branding;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SiteBrandingRepository
{
    public const CACHE_KEY = 'platform.site_branding';

    /**
     * @return array{
     *   site_name: string,
     *   site_short_name: string,
     *   heading: string,
     *   tagline: string,
     *   meta_description: string,
     *   favicon_media_id: ?int,
     *   logo_light_media_id: ?int,
     *   logo_dark_media_id: ?int
     * }
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return [
                'site_name' => (string) SystemSetting::get('site_name', config('app.name', '7th Trade Hub')),
                'site_short_name' => (string) SystemSetting::get('site_short_name', 'Trade Hub'),
                'heading' => (string) SystemSetting::get('site_heading', 'The Ultimate Digital Service Marketplace'),
                'tagline' => (string) SystemSetting::get('site_tagline', 'Connecting markets, empowering traders.'),
                'meta_description' => (string) SystemSetting::get(
                    'site_meta_description',
                    'NGN wallet marketplace. Deposit, buy with escrow, sell digital products and services.'
                ),
                'favicon_media_id' => $this->nullableInt(SystemSetting::get('favicon_media_id')),
                'logo_light_media_id' => $this->nullableInt(SystemSetting::get('logo_light_media_id')),
                'logo_dark_media_id' => $this->nullableInt(SystemSetting::get('logo_dark_media_id')),
            ];
        });
    }

    public function siteName(): string
    {
        return $this->all()['site_name'];
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function save(array $values): void
    {
        $map = [
            'site_name' => 'site_name',
            'site_short_name' => 'site_short_name',
            'heading' => 'site_heading',
            'tagline' => 'site_tagline',
            'meta_description' => 'site_meta_description',
            'favicon_media_id' => 'favicon_media_id',
            'logo_light_media_id' => 'logo_light_media_id',
            'logo_dark_media_id' => 'logo_dark_media_id',
        ];

        foreach ($map as $input => $key) {
            if (array_key_exists($input, $values)) {
                SystemSetting::set($key, (string) ($values[$input] ?? ''));
            }
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
