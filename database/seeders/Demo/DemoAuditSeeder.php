<?php

namespace Database\Seeders\Demo;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;

class DemoAuditSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        $actions = [
            [
                'admin' => 'moderator',
                'action' => 'listing.approved',
                'module' => 'catalog',
                'reason' => 'Listing meets marketplace quality guidelines.',
                'months' => 4,
            ],
            [
                'admin' => 'moderator',
                'action' => 'listing.suspended',
                'module' => 'catalog',
                'reason' => 'Fraudulent claims detected in description.',
                'months' => 2,
            ],
            [
                'admin' => 'compliance',
                'action' => 'kyc.rejected',
                'module' => 'compliance',
                'reason' => 'ID photo too blurry.',
                'months' => 4,
            ],
            [
                'admin' => 'compliance',
                'action' => 'kyc.approved',
                'module' => 'compliance',
                'reason' => 'Documents verified for level 2.',
                'months' => 5,
            ],
            [
                'admin' => 'compliance',
                'action' => 'kyc.override',
                'module' => 'compliance',
                'reason' => 'Manual level adjustment after appeal review.',
                'months' => 1,
            ],
            [
                'admin' => 'finance',
                'action' => 'escrow.released',
                'module' => 'finance',
                'reason' => 'Buyer confirmed delivery.',
                'months' => 3,
            ],
            [
                'admin' => 'finance',
                'action' => 'funding.approved',
                'module' => 'finance',
                'reason' => 'Bank credit matched reference.',
                'months' => 2,
            ],
            [
                'admin' => 'support',
                'action' => 'support.replied',
                'module' => 'support',
                'reason' => 'Staff reply sent on payment ticket.',
                'months' => 1,
            ],
            [
                'admin' => 'super',
                'action' => 'settings.updated',
                'module' => 'system',
                'reason' => 'Adjusted platform fee percent for demo realism.',
                'months' => 0,
            ],
        ];

        foreach ($actions as $i => $row) {
            $admin = $ctx->admin($row['admin']);
            $at = $timeline->monthsAgo($row['months'], 16, 15);

            $log = AuditLog::query()->create([
                'admin_id' => $admin->id,
                'actor_id' => $admin->id,
                'actor_type' => 'admin',
                'action' => $row['action'],
                'module' => $row['module'],
                'model_type' => null,
                'model_id' => null,
                'old_values' => null,
                'new_values' => ['demo' => true, 'note' => $row['reason']],
                'ip' => '198.51.100.'.($i + 10),
                'user_agent' => 'DemoSeed/1.0',
                'device' => 'desktop',
                'browser' => 'Chrome',
                'country' => 'NG',
                'reason' => $row['reason'],
                'correlation_id' => 'demo-corr-'.$i,
                'request_id' => 'demo-req-'.$i,
            ]);
            $timeline->stamp($log, $at);
        }

        // Ensure a settings value exists for the settings audit story
        SystemSetting::query()->updateOrCreate(
            ['key' => 'platform_fee_percent'],
            ['value' => '2.5']
        );

        $ctx->note('✓ Admin audit narrative created');
    }
}
