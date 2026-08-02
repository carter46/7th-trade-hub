<?php

namespace App\Console\Commands;

use App\Modules\Wallet\Services\CryptoPriceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmCryptoPrices extends Command
{
    protected $signature = 'app:warm-crypto-prices';

    protected $description = 'Refresh cached crypto price ticker data';

    public function handle(CryptoPriceService $prices): int
    {
        Cache::forget('crypto_prices_ngn');
        Cache::forget('crypto_prices_ngn_fallback');
        $data = $prices->getPrices();
        $this->info('Crypto price cache warmed ('.count($data).' coins).');

        return self::SUCCESS;
    }
}
