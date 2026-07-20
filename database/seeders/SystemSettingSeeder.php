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
        ];

        foreach ($defaults as $key => $value) {
            if (SystemSetting::where('key', $key)->doesntExist()) {
                SystemSetting::set($key, $value);
            }
        }
    }
}
