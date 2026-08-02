<?php

namespace App\Console\Commands;

use App\Modules\Wallet\Services\Blockchain\WalletBalanceMonitorService;
use Illuminate\Console\Command;

class PollWalletBalances extends Command
{
    protected $signature = 'crypto:poll-balances';

    protected $description = 'Poll current on-chain balances for crypto deposit wallets';

    public function handle(WalletBalanceMonitorService $monitor): int
    {
        $result = $monitor->poll();
        $this->info(sprintf(
            'Polled %d wallet(s); %d updated; %d error(s).',
            $result['wallets'],
            $result['updated'],
            count($result['errors'])
        ));
        foreach ($result['errors'] as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
