<?php

namespace App\Services\Notifications\Channels;

use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Notifications\EmailIdentityResolver;
use App\Services\Notifications\NotificationDedupeService;
use App\Services\Notifications\NotificationDeliveryTracer;
use App\Services\Notifications\NotificationMessage;

class DatabaseChannel implements NotificationChannel
{
    public function __construct(
        private NotificationDedupeService $dedupe,
        private NotificationDeliveryTracer $tracer,
        private EmailIdentityResolver $identityResolver,
    ) {}

    public function send(NotificationMessage $message, string $audience, ?iterable $recipients = null): void
    {
        if ($audience === 'admin') {
            $profile = $this->identityResolver->resolveProfileForType($message->type);

            if ($this->dedupe->shouldSkip($message->type, $message->dedupeKey, 'database')) {
                $this->tracer->record(
                    notificationType: $message->type,
                    channel: 'database',
                    status: 'deduped',
                    profile: $profile->value,
                    dedupeKey: $message->dedupeKey,
                    event: $message->meta['event'] ?? null,
                );

                return;
            }

            if (! $this->dedupe->tryClaim($message->type, $message->dedupeKey, 'database')) {
                $this->tracer->record(
                    notificationType: $message->type,
                    channel: 'database',
                    status: 'deduped',
                    profile: $profile->value,
                    dedupeKey: $message->dedupeKey,
                    event: $message->meta['event'] ?? null,
                );

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

            $this->tracer->record(
                notificationType: $message->type,
                channel: 'database',
                status: 'sent',
                profile: $profile->value,
                dedupeKey: $message->dedupeKey,
                event: $message->meta['event'] ?? null,
            );

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

}
