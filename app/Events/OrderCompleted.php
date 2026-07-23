<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $orderId,
        public ?int $buyerId = null,
        public ?int $sellerId = null,
    ) {}
}
