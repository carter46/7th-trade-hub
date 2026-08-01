<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PurgeAnonymizedUsersCommand extends Command
{
    protected $signature = 'users:purge-anonymized
                            {--hours=24 : Remove anonymized tombstones at least this many hours old (0 = all)}';

    protected $description = 'Hard-delete anonymized user tombstones after the retention window (default 24h)';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $result = User::purgeAnonymizedOlderThanHours($hours);

        $label = $hours <= 0 ? 'all anonymized' : "older than {$hours}h";
        $this->info("Purged {$result['purged']} anonymized user(s) ({$label}).");
        if ($result['failed'] > 0) {
            $this->warn("Failed to purge {$result['failed']} user(s). Check the log.");
        }

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
