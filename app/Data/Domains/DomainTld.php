<?php

namespace App\Data\Domains;

final readonly class DomainTld
{
    /**
     * @param  list<string>  $supportedProviderKeys
     */
    public function __construct(
        public string $tld,
        public string $primaryProviderKey,
        public array $supportedProviderKeys,
        public ?float $registrationCost = null,
        public string $currency = 'USD',
        public bool $purchasable = true,
    ) {}

    public function label(): string
    {
        return '.'.ltrim($this->tld, '.');
    }

    public function supportsProvider(string $providerKey): bool
    {
        return in_array($providerKey, $this->supportedProviderKeys, true);
    }
}
