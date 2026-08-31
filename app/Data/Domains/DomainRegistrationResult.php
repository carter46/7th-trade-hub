<?php

namespace App\Data\Domains;

readonly class DomainRegistrationResult
{
    public function __construct(
        public bool $success,
        public ?string $providerReference = null,
        public ?string $errorMessage = null,
        public array $providerMeta = [],
    ) {}
}
