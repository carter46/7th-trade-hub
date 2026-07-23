<?php

namespace App\Modules\Marketplace\Services;

use App\Models\User;
use App\Models\UserNotification;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationMessage;

/**
 * @deprecated Prefer NotificationDispatcher directly for new code.
 */
class NotificationService
{
    public function __construct(private NotificationDispatcher $dispatcher) {}

    /**
     * @param  list<string>  $channels
     */
    public function send(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        array $channels = ['database']
    ): UserNotification {
        $this->dispatcher->notifyUser(
            $user,
            new NotificationMessage(
                type: $type,
                title: $title,
                body: $body,
                actionUrl: $actionUrl,
            ),
            $channels
        );

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->latest('id')
            ->firstOrFail();
    }
}
