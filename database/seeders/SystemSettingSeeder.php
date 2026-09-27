<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Services\Branding\SiteBrandingRepository;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'withdrawal_min_amount' => '100',
            'withdrawal_max_amount' => '1000000',
            'deposit_min_amount' => '100',
            'live_chat_provider' => 'none',
            'smartsupp_key' => '',
            'jivo_widget_id' => '',
            'contact_phone' => '',
            'contact_email' => '',
            'contact_email_alt' => '',
            'site_name' => config('app.name', '7th Trade Hub'),
            'site_short_name' => 'Trade Hub',
            'site_heading' => SiteBrandingRepository::DEFAULT_HEADING,
            'site_tagline' => SiteBrandingRepository::DEFAULT_TAGLINE,
            'site_meta_description' => SiteBrandingRepository::DEFAULT_META_DESCRIPTION,
            'contact_timezone' => 'Africa/Lagos',
        ];

        foreach ($defaults as $key => $value) {
            if (SystemSetting::where('key', $key)->doesntExist()) {
                SystemSetting::set($key, $value);
            }
        }
    }
}
