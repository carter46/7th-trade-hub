<?php

namespace App\Support\Domains;

class DomainRegistrationContacts
{
    /**
     * @return array<string, mixed>
     */
    public static function profile(): array
    {
        return config('domains.registration_contacts', []);
    }

    /**
     * @return list<string>
     */
    public static function nameservers(): array
    {
        return array_values(array_filter(config('domains.default_nameservers', [])));
    }

    /**
     * @return array<string, mixed> Name.com contact block
     */
    public static function forNameCom(string $role = 'registrant'): array
    {
        $p = self::profile();

        return [
            'firstName' => (string) ($p['first_name'] ?? 'Domain'),
            'lastName' => (string) ($p['last_name'] ?? 'Admin'),
            'companyName' => (string) ($p['company'] ?? ''),
            'email' => (string) ($p['email'] ?? ''),
            'phone' => (string) ($p['phone'] ?? ''),
            'address1' => (string) ($p['address'] ?? ''),
            'city' => (string) ($p['city'] ?? ''),
            'state' => (string) ($p['state'] ?? ''),
            'zip' => (string) ($p['zip'] ?? ''),
            'country' => (string) ($p['country'] ?? 'NG'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function forDomainNameApi(): array
    {
        $p = self::profile();
        $base = [
            'firstName' => (string) ($p['first_name'] ?? 'Domain'),
            'lastName' => (string) ($p['last_name'] ?? 'Admin'),
            'companyName' => (string) ($p['company'] ?? ''),
            'eMail' => (string) ($p['email'] ?? ''),
            'phone' => (string) ($p['phone'] ?? ''),
            'address' => (string) ($p['address'] ?? ''),
            'city' => (string) ($p['city'] ?? ''),
            'state' => (string) ($p['state'] ?? ''),
            'postalCode' => (string) ($p['zip'] ?? ''),
            'country' => (string) ($p['country'] ?? 'NG'),
        ];

        return collect(['Registrant', 'Administrative', 'Technical', 'Billing'])
            ->map(fn (string $type) => array_merge($base, ['contactType' => $type]))
            ->all();
    }
}
