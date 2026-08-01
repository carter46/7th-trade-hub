<?php

namespace App\Console\Commands;

use App\Modules\Support\Services\SupportAttachmentService;
use Illuminate\Console\Command;

class PruneSupportAttachments extends Command
{
    protected $signature = 'support:prune-attachments';

    protected $description = 'Delete expired support ticket evidence files (72h TTL)';

    public function handle(SupportAttachmentService $attachments): int
    {
        $count = $attachments->pruneExpired();
        $this->info("Pruned {$count} support attachment(s).");

        return self::SUCCESS;
    }
}
