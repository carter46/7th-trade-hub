<?php

namespace App\Console\Commands;

use App\Modules\Wallet\Services\CryptoPriceService;
use Illuminate\Console\Command;

class WarmCryptoPrices extends Command
{
    protected $signature = 'app:warm-crypto-prices';

    protected $description = 'Refresh cached crypto price ticker data';

    public function handle(CryptoPriceService $prices): int
    {
        $prices->getPrices();
        $this->info('Crypto price cache warmed.');

        return self::SUCCESS;
    }
}
