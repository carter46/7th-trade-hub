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
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} crypto sell quote(s).");

        return self::SUCCESS;
    }
}
