<?php

namespace App\Services\Notifications;

use App\Models\AdminNotification;
use App\Models\NotificationDedupeClaim;
use App\Models\NotificationDeliveryLog;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class NotificationDedupeService
{
    public function shouldSkip(string $type, ?string $dedupeKey, string $channel = 'mail'): bool
    {
        if (! $dedupeKey) {
            return false;
        }

        $since = Carbon::now()->subDay();

        if (Schema::hasTable('notification_dedupe_claims')) {
            $claimed = NotificationDedupeClaim::query()
                ->where('notification_type', $type)
                ->where('dedupe_key', $dedupeKey)
                ->where('channel', $channel)
                ->where('created_at', '>=', $since)
                ->exists();

            if ($claimed) {
                return true;
            }
        }

        if (Schema::hasTable('notification_delivery_logs')) {
            $exists = NotificationDeliveryLog::query()
                ->where('notification_type', $type)
                ->where('dedupe_key', $dedupeKey)
                ->where('channel', $channel)
                ->where('status', 'sent')
                ->where('created_at', '>=', $since)
                ->exists();

            if ($exists) {
                return true;
            }
        }

        if ($channel === 'database') {
            return AdminNotification::query()
                ->where('type', $type)
                ->where('created_at', '>=', $since)
                ->where('meta->dedupe_key', $dedupeKey)
                ->exists();
        }

        return false;
    }

    public function tryClaim(string $type, ?string $dedupeKey, string $channel = 'mail'): bool
    {
        if (! $dedupeKey) {
            return true;
        }

        if ($this->shouldSkip($type, $dedupeKey, $channel)) {
            return false;
        }

        if (! Schema::hasTable('notification_dedupe_claims')) {
            return true;
        }

        try {
            NotificationDedupeClaim::query()->create([
                'notification_type' => $type,
                'dedupe_key' => $dedupeKey,
                'channel' => $channel,
                'created_at' => now(),
            ]);

            return true;
        } catch (UniqueConstraintViolationException) {
            return false;
        }
    }
}
