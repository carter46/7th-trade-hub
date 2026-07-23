<?php

namespace App\Services\Notifications\Channels;

use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Log;

class SmsChannel implements NotificationChannel
{
    public function send(NotificationMessage $message, string $audience, ?iterable $recipients = null): void
    {
        Log::debug('notification.sms_stub', [
            'audience' => $audience,
            'type' => $message->type,
            'title' => $message->title,
        ]);
    }
}
