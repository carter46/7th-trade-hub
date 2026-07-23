<?php

namespace Database\Seeders\Demo;

use App\Models\AdminNotification;
use App\Models\UserNotification;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;

class DemoNotificationsSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        $types = [
            ['type' => 'wallet.funded', 'title' => 'Wallet funded', 'body' => 'Your wallet credit was approved.'],
            ['type' => 'order', 'title' => 'Order update', 'body' => 'Your marketplace order status changed.'],
            ['type' => 'escrow', 'title' => 'Escrow update', 'body' => 'Escrow status changed on your order.'],
            ['type' => 'ticket.replied', 'title' => 'Support replied', 'body' => 'A staff member replied to your ticket.'],
            ['type' => 'listing', 'title' => 'Listing update', 'body' => 'Your listing moderation status changed.'],
            ['type' => 'kyc', 'title' => 'KYC update', 'body' => 'Your KYC submission was reviewed.'],
        ];

        foreach ($ctx->members() as $key => $user) {
            if ($key === 'emily') {
                continue;
            }

            foreach ($types as $i => $t) {
                $at = $timeline->daysAgo(2 + $i * 3, 16);
                $n = UserNotification::query()->create([
                    'user_id' => $user->id,
                    'type' => $t['type'],
                    'title' => $t['title'],
                    'body' => $t['body'],
                    'action_url' => '/dashboard',
                    'read_at' => $i % 2 === 0 ? $at->copy()->addHour() : null,
                ]);
                $ctx->stamp($n, $at, ['read_at' => $n->read_at]);
            }
        }

        $adminTypes = [
            ['type' => 'escrow.disputed', 'title' => 'Escrow dispute opened', 'body' => 'A buyer opened a dispute.'],
            ['type' => 'ticket.opened', 'title' => 'New support ticket', 'body' => 'A customer opened a ticket.'],
            ['type' => 'wallet.funded', 'title' => 'Wallet funded', 'body' => 'A funding was approved.'],
            ['type' => 'listing.rejected', 'title' => 'Listing rejected', 'body' => 'A listing was rejected in review.'],
        ];

        foreach ($adminTypes as $i => $t) {
            $at = $timeline->daysAgo(1 + $i, 9);
            $n = AdminNotification::query()->create([
                'type' => $t['type'],
                'title' => $t['title'],
                'body' => $t['body'],
                'action_url' => '/admin',
                'meta' => ['demo' => true],
                'read_at' => $i === 0 ? null : $at->copy()->addHours(2),
            ]);
            $ctx->stamp($n, $at, ['read_at' => $n->read_at]);
        }

        $ctx->note('✓ User and admin notifications created');
    }
}
