<?php

namespace App\Contracts\Domains;

use App\Data\Domains\DomainAvailabilityResult;
use App\Data\Domains\DomainRegistrationQuote;
use App\Data\Domains\DomainRegistrationResult;
use App\Data\Domains\DomainTld;
use App\Models\DomainProvider;

interface DomainProviderInterface
{
    public function key(): string;

    /**
     * @return list<DomainTld>
     */
    public function listTlds(DomainProvider $provider): array;

    public function checkAvailability(DomainProvider $provider, string $fqdn): DomainAvailabilityResult;

    public function getRegistrationQuote(DomainProvider $provider, string $fqdn, DomainAvailabilityResult $availability): DomainRegistrationQuote;

    public function testConnection(DomainProvider $provider): bool;

    /**
     * @param  array<string, mixed>  $context  provider_cost, premium, purchase_type, idempotency_key, quote_id
     */
    public function registerDomain(DomainProvider $provider, string $fqdn, array $context = []): DomainRegistrationResult;

    /**
     * @return list<string>
     */
    public function getNameservers(DomainProvider $provider, string $fqdn): array;

    /**
     * @param  list<string>  $nameservers
     */
    public function updateNameservers(DomainProvider $provider, string $fqdn, array $nameservers): void;
}
