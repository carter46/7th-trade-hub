<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WalletFunding;

class WalletFundingPolicy
{
    public function view(User $user, WalletFunding $funding): bool
    {
        return $user->id === $funding->user_id;
    }
}
