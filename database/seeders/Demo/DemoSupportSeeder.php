<?php

namespace Database\Seeders\Demo;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoConversationScripts;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;

class DemoSupportSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        $support = $ctx->admin('support');
        $scripts = DemoConversationScripts::pool();
        $statuses = ['open', 'pending', 'awaiting_user', 'resolved', 'closed'];
        $priorities = ['normal', 'high', 'urgent'];
        $members = $ctx->members()->filter(fn ($u, $k) => $k !== 'emily')->values();
        $count = 0;

        for ($i = 0; $i < 40; $i++) {
            $script = $scripts[$i % count($scripts)];
            $user = $members[$i % $members->count()];
            $status = $statuses[$i % count($statuses)];
            $opened = $timeline->monthsAgo(min(5, (int) ($i % 6)), 4 + ($i % 20), 10);

            $ticket = SupportTicket::query()->create([
                'user_id' => $user->id,
                'category' => $script['category'],
                'subject' => $script['subject'].($i > 4 ? ' #'.($i + 1) : ''),
                'body' => $script['body'],
                'status' => $status,
                'priority' => $priorities[$i % count($priorities)],
                'assigned_to' => $support->id,
            ]);
            $ctx->stamp($ticket, $opened);

            $replyAt = $opened->copy()->addHours(3);
            foreach ($script['replies'] as $rIdx => $reply) {
                $authorId = match ($reply['role']) {
                    'admin' => $support->id,
                    'seller' => $ctx->members()->first(fn ($u, $k) => in_array($k, ['michael', 'sarah'], true))?->id
                        ?? $user->id,
                    default => $user->id,
                };

                $row = SupportTicketReply::query()->create([
                    'support_ticket_id' => $ticket->id,
                    'user_id' => $authorId,
                    'body' => $reply['body'],
                    'is_staff' => $reply['role'] === 'admin',
                ]);
                $ctx->stamp($row, $replyAt->copy()->addHours($rIdx * 5));
            }

            if (in_array($status, ['resolved', 'closed'], true)) {
                $resolvedAt = $opened->copy()->addDays(3);
                $ctx->stamp($ticket, $opened, [
                    'updated_at' => $resolvedAt,
                ]);
            }

            $count++;
        }

        $ctx->ticketCount = $count;
        $ctx->note('✓ Support tickets created ('.$count.' with scripted conversations)');
    }
}
