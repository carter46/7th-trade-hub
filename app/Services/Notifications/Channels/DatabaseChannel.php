<?php

namespace App\Services\Notifications\Channels;

use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Carbon;

class DatabaseChannel implements NotificationChannel
{
    public function send(NotificationMessage $message, string $audience, ?iterable $recipients = null): void
    {
        if ($audience === 'admin') {
            if ($message->dedupeKey && $this->adminDedupeExists($message)) {
                return;
            }

            AdminNotification::query()->create([
                'type' => $message->type,
                'title' => $message->title,
                'body' => $message->body,
                'action_url' => $message->actionUrl,
                'meta' => array_filter([
                    ...$message->meta,
                    'dedupe_key' => $message->dedupeKey,
                    'priority' => $message->priority,
                ], fn ($v) => $v !== null),
            ]);

            return;
        }

        foreach ($recipients ?? [] as $user) {
            if (! $user instanceof User) {
                continue;
            }

            UserNotification::query()->create([
                'user_id' => $user->id,
                'type' => $message->type,
                'title' => $message->title,
                'body' => $message->body,
                'action_url' => $message->actionUrl,
            ]);
        }
    }

    private function adminDedupeExists(NotificationMessage $message): bool
    {
        $since = Carbon::now()->startOfDay();

        return AdminNotification::query()
            ->where('type', $message->type)
            ->where('created_at', '>=', $since)
            ->where('meta->dedupe_key', $message->dedupeKey)
            ->exists();
    }
}
