<?php

namespace App\Services\Notifications;

use App\Models\NotificationDeliveryLog;
use Illuminate\Support\Facades\Schema;

class NotificationDeliveryTracer
{
    public function record(
        string $notificationType,
        string $channel,
        string $status,
        ?string $profile = null,
        ?string $recipient = null,
        ?string $dedupeKey = null,
        ?string $event = null,
        ?string $failureReason = null,
        array $meta = [],
    ): void {
        if (! Schema::hasTable('notification_delivery_logs')) {
            return;
        }

        NotificationDeliveryLog::query()->create([
            'event' => $event,
            'notification_type' => $notificationType,
            'profile' => $profile,
            'recipient' => $recipient,
            'channel' => $channel,
            'status' => $status,
            'dedupe_key' => $dedupeKey,
            'failure_reason' => $failureReason,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }
}
