<?php

namespace App\Data\Domains;

final readonly class DomainAvailabilityResult
{
    public function __construct(
        public string $fqdn,
        public bool $available,
        public bool $purchasable,
        public string $purchaseType,
        public bool $premium,
        public ?float $purchasePrice,
        public string $currency,
        public ?string $message = null,
    ) {}

    public function isRegistration(): bool
    {
        return $this->purchaseType === 'registration';
    }
}
