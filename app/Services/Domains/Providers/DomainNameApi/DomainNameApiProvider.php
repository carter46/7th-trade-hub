<?php

namespace App\Services\Domains\Providers\DomainNameApi;

use App\Contracts\Domains\DomainProviderInterface;
use App\Data\Domains\DomainAvailabilityResult;
use App\Data\Domains\DomainRegistrationQuote;
use App\Data\Domains\DomainRegistrationResult;
use App\Data\Domains\DomainTld;
use App\Models\DomainProvider;
use App\Services\Domains\Exceptions\DomainBusinessException;
use App\Support\Domains\DomainRegistrationContacts;

class DomainNameApiProvider implements DomainProviderInterface
{
    public function __construct(
        private DomainNameApiClient $client,
    ) {}

    public function key(): string
    {
        return 'domainnameapi';
    }

    public function listTlds(DomainProvider $provider): array
    {
        $items = [];
        $skip = 0;
        $pageSize = 100;

        do {
            $payload = $this->client->listTlds($provider, $skip, $pageSize);
            $rows = $payload['items'] ?? $payload['data'] ?? [];

            if (! is_array($rows)) {
                break;
            }

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $tld = ltrim(strtolower((string) ($row['name'] ?? '')), '.');
                if ($tld === '') {
                    continue;
                }

                $price = $this->registrationPriceFromRow($row);
                if ($price === null || $price <= 0) {
                    continue;
                }

                $items[$tld] = new DomainTld(
                    tld: $tld,
                    primaryProviderKey: $provider->key,
                    supportedProviderKeys: [$provider->key],
                    registrationCost: $price,
                    currency: 'USD',
                    purchasable: true,
                );
            }

            $skip += $pageSize;
            $total = (int) ($payload['totalCount'] ?? count($rows));
        } while (count($rows) >= $pageSize && $skip < $total && $skip <= 2000);

        ksort($items);

        return array_values($items);
    }

    public function checkAvailability(DomainProvider $provider, string $fqdn): DomainAvailabilityResult
    {
        $payload = $this->client->searchDomain($provider, $fqdn);
        $info = $payload['info'] ?? null;

        if (! is_array($info)) {
            return new DomainAvailabilityResult(
                fqdn: $fqdn,
                available: false,
                purchasable: false,
                purchaseType: 'unknown',
                premium: false,
                purchasePrice: null,
                currency: 'USD',
                message: (string) ($payload['reason'] ?? 'Unable to check domain availability.'),
            );
        }

        $status = strtolower((string) ($info['status'] ?? ''));
        $available = ($payload['success'] ?? false) && in_array($status, ['available', 'free'], true);
        $premium = (bool) ($info['isPremium'] ?? false);
        $price = isset($info['price']) ? (float) $info['price'] : null;

        return new DomainAvailabilityResult(
            fqdn: strtolower((string) ($info['domainName'] ?? $fqdn)),
            available: $available,
            purchasable: $available,
            purchaseType: 'registration',
            premium: $premium,
            purchasePrice: $price,
            currency: strtoupper((string) ($info['currency'] ?? 'USD')),
            message: $available ? null : (string) ($info['reason'] ?? 'Domain is not available for registration.'),
        );
    }

    public function getRegistrationQuote(DomainProvider $provider, string $fqdn, DomainAvailabilityResult $availability): DomainRegistrationQuote
    {
        if (! $availability->purchasable || $availability->purchaseType !== 'registration') {
            throw new DomainBusinessException('Domain is not available for registration.');
        }

        $cost = $availability->purchasePrice;
        if ($cost === null || $cost <= 0) {
            $fresh = $this->checkAvailability($provider, $fqdn);
            $cost = $fresh->purchasePrice;
        }

        if ($cost === null || $cost <= 0) {
            throw new DomainBusinessException('Unable to determine domain registration price.');
        }

        return new DomainRegistrationQuote(
            fqdn: $fqdn,
            providerCost: $cost,
            providerCurrency: $availability->currency ?: 'USD',
            premium: $availability->premium,
            purchaseType: 'registration',
            providerMeta: ['pricing_source' => 'search'],
        );
    }

    public function testConnection(DomainProvider $provider): bool
    {
        $this->client->listTlds($provider, 0, 1);

        return true;
    }

    public function registerDomain(DomainProvider $provider, string $fqdn, array $context = []): DomainRegistrationResult
    {
        $availability = $this->checkAvailability($provider, $fqdn);
        if (! $availability->available || ! $availability->isRegistration()) {
            return new DomainRegistrationResult(false, errorMessage: 'Domain is no longer available for registration.');
        }

        $nameservers = DomainRegistrationContacts::nameservers();
        if ($nameservers === []) {
            return new DomainRegistrationResult(false, errorMessage: 'Default nameservers are not configured.');
        }

        $contactProfile = $context['registrant_contact'] ?? null;
        if (! is_array($contactProfile)) {
            return new DomainRegistrationResult(false, errorMessage: 'Registrant contact details are missing.');
        }

        try {
            DomainRegistrationContacts::assertComplete($contactProfile);
        } catch (\InvalidArgumentException $e) {
            return new DomainRegistrationResult(false, errorMessage: $e->getMessage());
        }

        try {
            $payload = $this->client->registerWithContacts($provider, [
                'domainName' => $fqdn,
                'period' => 1,
                'nameServers' => $nameservers,
                'contacts' => DomainRegistrationContacts::forDomainNameApi($contactProfile),
            ]);

            if (! ($payload['success'] ?? false)) {
                return new DomainRegistrationResult(
                    false,
                    errorMessage: (string) ($payload['reason'] ?? $payload['operationMessage'] ?? 'Registration failed.'),
                    providerMeta: $payload,
                );
            }

            return new DomainRegistrationResult(
                true,
                providerReference: (string) ($payload['data']['domainId'] ?? $payload['data']['id'] ?? $fqdn),
                providerMeta: $payload,
            );
        } catch (\Throwable $e) {
            return new DomainRegistrationResult(false, errorMessage: $e->getMessage());
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function registrationPriceFromRow(array $row): ?float
    {
        $prices = $row['prices'] ?? [];
        if (! is_array($prices)) {
            return null;
        }

        foreach ($prices as $group) {
            if (! is_array($group)) {
                continue;
            }

            $register = $group['register'] ?? [];
            if (! is_array($register)) {
                continue;
            }

            foreach ($register as $item) {
                if (! is_array($item)) {
                    continue;
                }

                if ((int) ($item['period'] ?? 1) === 1 && isset($item['price'])) {
                    return (float) $item['price'];
                }
            }
        }

        return null;
    }
}
