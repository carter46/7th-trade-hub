<?php

namespace App\Services\Domains;

use App\Enums\DomainCommerceMode;

/**
 * Single authoritative provider vs manual domain commerce decision for a request.
 */
class DomainCommerceModeResolver
{
    public function __construct(
        private DomainProviderManager $providers,
    ) {}

    public function current(): DomainCommerceMode
    {
        return $this->providers->hasEnabledProviders()
            ? DomainCommerceMode::Provider
            : DomainCommerceMode::Manual;
    }

    public function isManual(): bool
    {
        return $this->current()->isManual();
    }

    public function isProvider(): bool
    {
        return $this->current()->isProvider();
    }
}
