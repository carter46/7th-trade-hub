<?php

namespace App\Modules\Wallet\Contracts;

use App\Models\User;

interface WalletProviderInterface
{
    public function createSubaccount(User $user, string $idempotencyKey): string;
}
