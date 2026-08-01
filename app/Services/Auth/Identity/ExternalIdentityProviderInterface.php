<?php

namespace App\Services\Auth\Identity;

interface ExternalIdentityProviderInterface
{
    public function name(): string;

    public function isAvailable(): bool;

    /**
     * @throws \InvalidArgumentException when the credential cannot be verified
     */
    public function verifyCredential(string $credential): VerifiedIdentity;
}
