<?php

namespace App\Modules\Wallet\Payments\Monnify;

use RuntimeException;

class MonnifyApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = '',
        public readonly array $payload = [],
    ) {
        parent::__construct($message);
    }
}
