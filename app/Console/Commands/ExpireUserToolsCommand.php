<?php

namespace App\Console\Commands;

use App\Enums\UserToolStatus;
use App\Models\UserTool;
use App\Services\SiteIntegrations\SubscriptionSyncService;
use App\Services\SiteIntegrations\UserToolLifecycleNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireUserToolsCommand extends Command
{
    protected $signature = 'site-integrations:expire-user-tools';

    protected $description = 'Mark expired user tools and push subscription status to external sites';

    public function handle(SubscriptionSyncService $sync, UserToolLifecycleNotifier $lifecycleNotifier): int
    {
        $ids = UserTool::query()
            ->where('status', '!=', UserToolStatus::Expired)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->pluck('id');

        $count = 0;
        foreach ($ids as $id) {
            $tool = DB::transaction(function () use ($id, $sync) {
                $tool = UserTool::query()
                    ->with(['integration', 'product', 'user'])
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if (! $tool) {
                    return null;
                }

                // Renew race: do not expire a tool that was extended after the query.
                if ($tool->status === UserToolStatus::Expired) {
                    return null;
                }

                if (! $tool->expires_at || $tool->expires_at->isFuture()) {
                    return null;
                }

                $tool->status = UserToolStatus::Expired;
                $tool->markSubscriptionEnded(UserTool::END_REASON_NATURAL);
                $tool->save();

                $sync->push($tool->fresh(['integration']));

                return $tool->fresh(['product', 'user']);
            });

            if ($tool === null) {
                continue;
            }

            $lifecycleNotifier->notifyNaturallyExpired($tool);

            $this->line(sprintf('Tool #%d expired; user notified', $id));
            $count++;
        }

        $this->info("Expired {$count} tool(s).");

        return self::SUCCESS;
    }
}
