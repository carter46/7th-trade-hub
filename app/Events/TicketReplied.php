<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $ticketId,
        public ?int $replierId = null,
        public bool $isAdminReply = false,
    ) {}
}
