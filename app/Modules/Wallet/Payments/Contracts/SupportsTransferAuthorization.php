<?php

namespace App\Modules\Wallet\Payments\Contracts;

interface SupportsTransferAuthorization
{
    /**
     * @param  array<string, mixed>  $initiateResult  Normalized responseBody from initiateTransfer
     */
    public function requiresTransferAuthorization(array $initiateResult): bool;

    /**
     * @return array<string, mixed> Normalized responseBody from validate-otp
     */
    public function authorizeTransfer(string $reference, string $authorizationCode): array;
}
