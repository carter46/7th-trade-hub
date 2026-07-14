<?php

namespace Database\Seeders;

use App\Modules\Wallet\Services\WalletService;
use Illuminate\Database\Seeder;

class PlatformWalletSeeder extends Seeder
{
    public function run(): void
    {
        app(WalletService::class)->getPlatformWallet();
    }
}
