<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListingRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $listingId,
        public ?int $adminId = null,
        public ?string $notes = null,
    ) {}
}
