<?php

namespace App\Modules\Wallet\Providers;

use App\Models\User;
use App\Modules\Wallet\Contracts\WalletProviderInterface;
use Illuminate\Support\Str;

class ManualProvider implements WalletProviderInterface
{
    public function createSubaccount(User $user, string $idempotencyKey): string
    {
        return 'manual_'.$idempotencyKey;
    }
}
