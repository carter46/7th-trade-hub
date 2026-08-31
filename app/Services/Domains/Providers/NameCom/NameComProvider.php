<?php

namespace App\Services\Domains\Providers\NameCom;

use App\Contracts\Domains\DomainProviderInterface;
use App\Data\Domains\DomainAvailabilityResult;
use App\Data\Domains\DomainRegistrationQuote;
use App\Data\Domains\DomainRegistrationResult;
use App\Data\Domains\DomainTld;
use App\Models\DomainProvider;
use App\Services\Domains\Exceptions\DomainBusinessException;
use App\Support\Domains\DomainRegistrationContacts;

class NameComProvider implements DomainProviderInterface
{
    public function __construct(
        private NameComClient $client,
    ) {}

    public function key(): string
    {
        return 'namecom';
    }

    public function listTlds(DomainProvider $provider): array
    {
        $items = [];
        $page = 1;

        do {
            $payload = $this->client->tldPricing($provider, $page, 100);
            $rows = $payload['tlds'] ?? $payload['results'] ?? [];

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $tld = ltrim(strtolower((string) ($row['tld'] ?? $row['name'] ?? '')), '.');
                if ($tld === '') {
                    continue;
                }

                $price = $row['registrationPrice'] ?? $row['registrationprice'] ?? null;
                if ($price === null) {
                    continue;
                }

                $items[$tld] = new DomainTld(
                    tld: $tld,
                    primaryProviderKey: $provider->key,
                    supportedProviderKeys: [$provider->key],
                    registrationCost: (float) $price,
                    currency: 'USD',
                    purchasable: true,
                );
            }

            $page++;
            $hasMore = count($rows) >= 100;
        } while ($hasMore && $page <= 20);

        ksort($items);

        return array_values($items);
    }

    public function checkAvailability(DomainProvider $provider, string $fqdn): DomainAvailabilityResult
    {
        $payload = $this->client->checkAvailability($provider, $fqdn);
        $result = $payload['results'][0] ?? null;

        if (! is_array($result)) {
            return new DomainAvailabilityResult(
                fqdn: $fqdn,
                available: false,
                purchasable: false,
                purchaseType: 'unknown',
                premium: false,
                purchasePrice: null,
                currency: 'USD',
                message: 'Unable to check domain availability.',
            );
        }

        $domainName = strtolower((string) ($result['domainName'] ?? $fqdn));
        $purchaseType = (string) ($result['purchaseType'] ?? 'registration');
        $premium = (bool) ($result['premium'] ?? false);
        $purchasable = (bool) ($result['purchasable'] ?? false);
        $price = isset($result['purchasePrice']) ? (float) $result['purchasePrice'] : null;

        return new DomainAvailabilityResult(
            fqdn: $domainName,
            available: $purchasable && $purchaseType === 'registration',
            purchasable: $purchasable,
            purchaseType: $purchaseType,
            premium: $premium,
            purchasePrice: $price,
            currency: 'USD',
            message: $purchasable ? null : 'Domain is not available for registration.',
        );
    }

    public function getRegistrationQuote(DomainProvider $provider, string $fqdn, DomainAvailabilityResult $availability): DomainRegistrationQuote
    {
        if (! $availability->purchasable || $availability->purchaseType !== 'registration') {
            throw new DomainBusinessException('Domain is not available for registration.');
        }

        $cost = $availability->purchasePrice;
        $meta = [];

        if ($availability->premium) {
            $pricing = $this->client->getPricing($provider, $fqdn, 1);
            $cost = isset($pricing['purchasePrice']) ? (float) $pricing['purchasePrice'] : null;
            $meta['pricing_source'] = 'getPricing';
        } elseif ($cost === null || $cost <= 0) {
            $pricing = $this->client->getPricing($provider, $fqdn, 1);
            $cost = isset($pricing['purchasePrice']) ? (float) $pricing['purchasePrice'] : null;
            $meta['pricing_source'] = 'getPricing';
        }

        if ($cost === null || $cost <= 0) {
            throw new DomainBusinessException('Unable to determine domain registration price.');
        }

        return new DomainRegistrationQuote(
            fqdn: $fqdn,
            providerCost: $cost,
            providerCurrency: 'USD',
            premium: $availability->premium,
            purchaseType: 'registration',
            providerMeta: $meta,
        );
    }

    public function testConnection(DomainProvider $provider): bool
    {
        $this->client->hello($provider);

        return true;
    }

    public function registerDomain(DomainProvider $provider, string $fqdn, array $context = []): DomainRegistrationResult
    {
        $availability = $this->checkAvailability($provider, $fqdn);
        if (! $availability->available || ! $availability->isRegistration()) {
            return new DomainRegistrationResult(false, errorMessage: 'Domain is no longer available for registration.');
        }

        $quote = $this->getRegistrationQuote($provider, $fqdn, $availability);
        $contact = DomainRegistrationContacts::forNameCom();

        $payload = [
            'domain' => [
                'domainName' => $fqdn,
                'contacts' => [
                    'registrant' => $contact,
                    'admin' => $contact,
                    'tech' => $contact,
                    'billing' => $contact,
                ],
            ],
            'years' => 1,
            'purchaseType' => 'registration',
        ];

        if ($quote->premium) {
            $payload['purchasePrice'] = $quote->providerCost;
        }

        $idempotencyKey = (string) ($context['idempotency_key'] ?? '');

        try {
            $response = $this->client->createDomain(
                $provider,
                $payload,
                $idempotencyKey !== '' ? $idempotencyKey : null,
            );

            return new DomainRegistrationResult(
                true,
                providerReference: (string) ($response['domain']['domainName'] ?? $fqdn),
                providerMeta: $response,
            );
        } catch (\Throwable $e) {
            return new DomainRegistrationResult(false, errorMessage: $e->getMessage());
        }
    }
}
