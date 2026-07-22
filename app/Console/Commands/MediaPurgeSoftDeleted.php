<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use App\Services\Media\MediaUsageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MediaPurgeSoftDeleted extends Command
{
    protected $signature = 'media:purge-soft-deleted
                            {--days=30 : Only purge assets soft-deleted at least this many days ago}
                            {--force : Actually delete files and rows}';

    protected $description = 'Purge soft-deleted media assets and their storage files';

    public function handle(MediaUsageService $usages): int
    {
        $days = max(0, (int) $this->option('days'));
        $force = (bool) $this->option('force');

        $query = MediaAsset::onlyTrashed()
            ->with('variants')
            ->where('deleted_at', '<=', now()->subDays($days));

        $count = 0;
        $skipped = 0;

        $query->chunkById(50, function ($assets) use ($usages, $force, &$count, &$skipped): void {
            foreach ($assets as $asset) {
                if ($usages->usageCount($asset->id) > 0) {
                    $skipped++;
                    continue;
                }

                if (! $force) {
                    $this->line("Would purge media #{$asset->id} ({$asset->original_name})");
                    $count++;
                    continue;
                }

                $asset->purgeFiles();
                $asset->forceDelete();
                $count++;

                Log::info('media.purge', ['media_asset_id' => $asset->id]);
            }
        });

        $this->info(($force ? 'Purged' : 'Would purge')." {$count} asset(s); skipped {$skipped} still referenced.");

        return self::SUCCESS;
    }
}
