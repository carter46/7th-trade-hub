<?php

namespace App\Services\Communications\Contact;

use App\Models\EmailIdentity;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class PlatformContactRepository
{
    public const CACHE_KEY = 'platform.contact';

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $support = EmailIdentity::forProfile(EmailIdentity::SUPPORT);
            $sales = EmailIdentity::forProfile(EmailIdentity::SALES);
            $info = EmailIdentity::forProfile(EmailIdentity::GENERAL);

            return [
                'email_info' => $info?->from_email
                    ?: (string) SystemSetting::get('contact_email_alt', ''),
                'email_support' => $support?->from_email
                    ?: (string) SystemSetting::get('contact_email', ''),
                'email_sales' => $sales?->from_email ?: '',
                'phone_support' => (string) SystemSetting::get(
                    'contact_phone_support',
                    SystemSetting::get('contact_phone', '')
                ),
                'phone_general' => (string) SystemSetting::get('contact_phone_general', ''),
                'phone_whatsapp' => (string) SystemSetting::get('contact_phone_whatsapp', ''),
                'address_street' => (string) SystemSetting::get('contact_address_street', ''),
                'address_city' => (string) SystemSetting::get('contact_address_city', ''),
                'address_state' => (string) SystemSetting::get('contact_address_state', ''),
                'address_country' => (string) SystemSetting::get('contact_address_country', ''),
                'address_postal' => (string) SystemSetting::get('contact_address_postal', ''),
                'latitude' => (string) SystemSetting::get('contact_latitude', ''),
                'longitude' => (string) SystemSetting::get('contact_longitude', ''),
                'maps_url' => (string) SystemSetting::get('contact_maps_url', ''),
                'maps_embed_url' => (string) SystemSetting::get('contact_maps_embed_url', ''),
                'support_hours' => (string) SystemSetting::get('contact_support_hours', ''),
                'timezone' => (string) SystemSetting::get('contact_timezone', 'Africa/Lagos'),
                'business_hours' => (string) SystemSetting::get('contact_business_hours', ''),
                'registration_number' => (string) SystemSetting::get('contact_registration_number', ''),
                'vat_number' => (string) SystemSetting::get('contact_vat_number', ''),
                'company_number' => (string) SystemSetting::get('contact_company_number', ''),
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function save(array $values): void
    {
        $keys = [
            'contact_phone_support',
            'contact_phone_general',
            'contact_phone_whatsapp',
            'contact_address_street',
            'contact_address_city',
            'contact_address_state',
            'contact_address_country',
            'contact_address_postal',
            'contact_latitude',
            'contact_longitude',
            'contact_maps_url',
            'contact_maps_embed_url',
            'contact_support_hours',
            'contact_timezone',
            'contact_business_hours',
            'contact_registration_number',
            'contact_vat_number',
            'contact_company_number',
        ];

        $inputMap = [
            'phone_support' => 'contact_phone_support',
            'phone_general' => 'contact_phone_general',
            'phone_whatsapp' => 'contact_phone_whatsapp',
            'address_street' => 'contact_address_street',
            'address_city' => 'contact_address_city',
            'address_state' => 'contact_address_state',
            'address_country' => 'contact_address_country',
            'address_postal' => 'contact_address_postal',
            'latitude' => 'contact_latitude',
            'longitude' => 'contact_longitude',
            'maps_url' => 'contact_maps_url',
            'maps_embed_url' => 'contact_maps_embed_url',
            'support_hours' => 'contact_support_hours',
            'timezone' => 'contact_timezone',
            'business_hours' => 'contact_business_hours',
            'registration_number' => 'contact_registration_number',
            'vat_number' => 'contact_vat_number',
            'company_number' => 'contact_company_number',
        ];

        foreach ($inputMap as $input => $key) {
            if (array_key_exists($input, $values)) {
                SystemSetting::set($key, (string) ($values[$input] ?? ''));
            }
        }

        // Keep legacy keys in sync for dual-read period.
        if (array_key_exists('phone_support', $values)) {
            SystemSetting::set('contact_phone', (string) ($values['phone_support'] ?? ''));
        }

        $this->flush();
    }

    public function formattedAddress(): string
    {
        $c = $this->all();
        $parts = array_filter([
            $c['address_street'],
            $c['address_city'],
            $c['address_state'],
            $c['address_postal'],
            $c['address_country'],
        ]);

        return implode(', ', $parts);
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
