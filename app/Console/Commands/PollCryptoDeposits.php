<?php

namespace App\Console\Commands;

use App\Modules\Wallet\Services\Blockchain\DepositMonitorService;
use Illuminate\Console\Command;

class PollCryptoDeposits extends Command
{
    protected $signature = 'crypto:poll-deposits';

    protected $description = 'Poll blockchain explorers for incoming crypto deposits';

    public function handle(DepositMonitorService $monitor): int
    {
        $result = $monitor->poll();
        $this->info(sprintf(
            'Polled %d wallet(s); %d new deposit(s); %d error(s).',
            $result['wallets'],
            $result['detected'],
            count($result['errors'])
        ));
        foreach ($result['errors'] as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
