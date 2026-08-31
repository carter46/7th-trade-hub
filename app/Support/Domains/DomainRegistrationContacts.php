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
     * @param  array<string, mixed>|null  $profile
     * @return array<string, mixed> Name.com contact block
     */
    public static function forNameCom(?array $profile = null): array
    {
        $p = $profile ?? self::profile();

        return [
            'firstName' => (string) ($p['first_name'] ?? ''),
            'lastName' => (string) ($p['last_name'] ?? ''),
            'companyName' => (string) ($p['company'] ?? ''),
            'email' => (string) ($p['email'] ?? ''),
            'phone' => (string) ($p['phone'] ?? ''),
            'address1' => (string) ($p['address'] ?? ''),
            'city' => (string) ($p['city'] ?? ''),
            'state' => (string) ($p['state'] ?? ''),
            'zip' => (string) ($p['zip'] ?? ''),
            'country' => (string) ($p['country'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $profile
     * @return list<array<string, mixed>>
     */
    public static function forDomainNameApi(?array $profile = null): array
    {
        $p = $profile ?? self::profile();
        $base = [
            'firstName' => (string) ($p['first_name'] ?? ''),
            'lastName' => (string) ($p['last_name'] ?? ''),
            'companyName' => (string) ($p['company'] ?? ''),
            'eMail' => (string) ($p['email'] ?? ''),
            'phone' => (string) ($p['phone'] ?? ''),
            'address' => (string) ($p['address'] ?? ''),
            'city' => (string) ($p['city'] ?? ''),
            'state' => (string) ($p['state'] ?? ''),
            'postalCode' => (string) ($p['zip'] ?? ''),
            'country' => (string) ($p['country'] ?? ''),
        ];

        return collect(['Registrant', 'Administrative', 'Technical', 'Billing'])
            ->map(fn (string $type) => array_merge($base, ['contactType' => $type]))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $contact
     */
    public static function assertComplete(array $contact): void
    {
        foreach (['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip', 'country'] as $key) {
            if (! filled($contact[$key] ?? null)) {
                throw new \InvalidArgumentException('Registrant contact details are incomplete.');
            }
        }
    }
}
