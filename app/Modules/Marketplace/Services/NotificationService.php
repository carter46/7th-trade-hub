<?php

namespace App\Modules\Marketplace\Services;

use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function send(User $user, string $type, string $title, ?string $body = null, ?string $actionUrl = null): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
        ]);
    }
}
