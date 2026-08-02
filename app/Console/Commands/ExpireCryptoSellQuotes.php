<?php

namespace App\Console\Commands;

use App\Models\CryptoSellRequest;
use Illuminate\Console\Command;

class ExpireCryptoSellQuotes extends Command
{
    protected $signature = 'app:expire-crypto-quotes';

    protected $description = 'Mark pending crypto sell requests with expired quotes as expired';

    public function handle(): int
    {
        $count = CryptoSellRequest::query()
            ->whereIn('status', [
                CryptoSellRequest::STATUS_WAITING_DEPOSIT,
                'pending',
            ])
            ->where('expires_at', '<', now())
            ->update(['status' => CryptoSellRequest::STATUS_EXPIRED]);

        $this->info("Expired {$count} crypto sell quote(s).");

        return self::SUCCESS;
    }
}
