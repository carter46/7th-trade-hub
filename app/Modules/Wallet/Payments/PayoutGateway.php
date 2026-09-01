<?php

namespace App\Modules\Wallet\Payments;

use App\Modules\Wallet\Payments\Contracts\PaymentRailInterface;
use App\Modules\Wallet\Payments\Contracts\SupportsTransferAuthorization;
use InvalidArgumentException;

final class PayoutGateway
{
    public function __construct(private PaymentRailInterface $rail) {}

    public static function from(PaymentRailInterface $rail): self
    {
        return new self($rail);
    }

    public function rail(): PaymentRailInterface
    {
        return $this->rail;
    }

    public function supportsAuthorization(): bool
    {
        return $this->rail instanceof SupportsTransferAuthorization;
    }

    /**
     * @param  array<string, mixed>  $initiateResult
     */
    public function requiresAuthorization(array $initiateResult): bool
    {
        if (! $this->rail instanceof SupportsTransferAuthorization) {
            return false;
        }

        return $this->rail->requiresTransferAuthorization($initiateResult);
    }

    public function authorizeTransferIfSupported(string $reference, string $authorizationCode): array
    {
        if (! $this->rail instanceof SupportsTransferAuthorization) {
            throw new InvalidArgumentException('Payment provider does not support transfer authorization.');
        }

        return $this->rail->authorizeTransfer($reference, $authorizationCode);
    }
}
