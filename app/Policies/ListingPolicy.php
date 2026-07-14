<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function update(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function submitForReview(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function purchase(User $user, Listing $listing): bool
    {
        if (! $listing->user_id) {
            return false;
        }

        if ((int) $listing->user_id === (int) $user->id) {
            return false;
        }

        return $listing->status === 'published' && $listing->is_active;
    }
}
