<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use Illuminate\Console\Command;

class PruneUserNotifications extends Command
{
    protected $signature = 'app:prune-notifications {--days=90 : Delete read notifications older than this many days}';

    protected $description = 'Remove old read user notifications';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $count = UserNotification::query()
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Pruned {$count} notification(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
