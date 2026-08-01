<?php

namespace App\Console\Commands;

use App\Services\Branding\PwaBrandingSync;
use Illuminate\Console\Command;

class SyncPwaBrandingCommand extends Command
{
    protected $signature = 'branding:sync-pwa';

    protected $description = 'Regenerate favicon, Apple touch, and PWA icons from admin branding media';

    public function handle(PwaBrandingSync $sync): int
    {
        if (! $sync->sync()) {
            $this->error('Branding icon sync failed. Check logs and that GD can write to public/.');

            return self::FAILURE;
        }

        $this->info('Branding icons and manifest.json updated.');

        foreach ($sync->publishedUrls() as $key => $url) {
            $this->line("  {$key}: {$url}");
        }

        return self::SUCCESS;
    }
}
