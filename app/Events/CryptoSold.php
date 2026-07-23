<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CryptoSold
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $userId,
        public int $transactionId,
        public float $amount,
        public string $currency = 'NGN',
    ) {}
}
