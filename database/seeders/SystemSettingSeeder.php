<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'platform_fee_percent' => '2.5',
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
            'site_heading' => 'The Ultimate Digital Service Marketplace',
            'site_tagline' => 'Connecting markets, empowering traders.',
            'site_meta_description' => 'NGN wallet marketplace. Deposit, buy with escrow, sell digital products and services.',
            'contact_timezone' => 'Africa/Lagos',
        ];

        foreach ($defaults as $key => $value) {
            if (SystemSetting::where('key', $key)->doesntExist()) {
                SystemSetting::set($key, $value);
            }
        }
    }
}
