<?php

namespace App\Services\Notifications\Channels;

use App\Models\User;
use App\Services\Notifications\NotificationMessage;

interface NotificationChannel
{
    /**
     * @param  'user'|'admin'  $audience
     * @param  iterable<User>|null  $recipients  Required for mail/sms/push; unused for shared admin DB inbox
     */
    public function send(NotificationMessage $message, string $audience, ?iterable $recipients = null): void;
}
