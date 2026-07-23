<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListingApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $listingId,
        public ?int $adminId = null,
    ) {}
}
