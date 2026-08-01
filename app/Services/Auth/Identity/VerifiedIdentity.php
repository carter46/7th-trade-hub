<?php

namespace App\Services\Auth\Identity;

final class VerifiedIdentity
{
    public function __construct(
        public readonly string $provider,
        public readonly string $providerUserId,
        public readonly string $email,
        public readonly bool $emailVerified,
        public readonly ?string $name = null,
        public readonly ?string $avatarUrl = null,
    ) {}
}
