<?php

namespace App\Support\Domains;

use InvalidArgumentException;

class DomainRegistrantContact
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $address,
        public readonly string $city,
        public readonly string $state,
        public readonly string $zip,
        public readonly string $country,
        public readonly ?string $company = null,
    ) {}

    /**
     * @return array<string, list<string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public static function validationRules(string $prefix = 'registrant'): array
    {
        return [
            "{$prefix}.first_name" => ['required', 'string', 'max:100'],
            "{$prefix}.last_name" => ['required', 'string', 'max:100'],
            "{$prefix}.email" => ['required', 'email', 'max:255'],
            "{$prefix}.phone" => ['required', 'string', 'max:32', 'regex:/^\+[\d\.]{7,20}$/'],
            "{$prefix}.address" => ['required', 'string', 'max:255'],
            "{$prefix}.city" => ['required', 'string', 'max:100'],
            "{$prefix}.state" => ['required', 'string', 'max:100'],
            "{$prefix}.zip" => ['required', 'string', 'max:20'],
            "{$prefix}.country" => ['required', 'string', 'size:2', 'alpha'],
            "{$prefix}.company" => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $phone = trim((string) ($data['phone'] ?? ''));
        $address = trim((string) ($data['address'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));
        $state = trim((string) ($data['state'] ?? ''));
        $zip = trim((string) ($data['zip'] ?? ''));
        $country = strtoupper(trim((string) ($data['country'] ?? '')));
        $company = trim((string) ($data['company'] ?? ''));

        if ($firstName === '' || $lastName === '' || $email === '' || $phone === ''
            || $address === '' || $city === '' || $state === '' || $zip === '' || strlen($country) !== 2) {
            throw new InvalidArgumentException('Enter complete registrant contact details for domain registration.');
        }

        return new self(
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            phone: $phone,
            address: $address,
            city: $city,
            state: $state,
            zip: $zip,
            country: $country,
            company: $company !== '' ? $company : null,
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toStorageArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'company' => $this->company,
        ];
    }
}
