<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderManualBankTransferPaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $orderId,
        public int $userId,
        public float $amount,
        public string $currency,
        public string $reference,
        public string $reason,
    ) {}
}
