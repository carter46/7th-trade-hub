<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListingSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $listingId,
        public ?int $userId = null,
    ) {}
}
