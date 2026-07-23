<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EscrowReleased
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $orderId,
        public ?int $sellerId = null,
    ) {}
}
