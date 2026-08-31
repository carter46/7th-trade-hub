<?php

namespace App\Data\Domains;

final readonly class DomainRegistrationQuote
{
    public function __construct(
        public string $fqdn,
        public float $providerCost,
        public string $providerCurrency,
        public bool $premium,
        public string $purchaseType,
        public array $providerMeta = [],
    ) {}
}
