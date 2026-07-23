<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Notifications\Channels\DatabaseChannel;
use App\Services\Notifications\Channels\MailChannel;
use App\Services\Notifications\Channels\NotificationChannel;
use App\Services\Notifications\Channels\PushChannel;
use App\Services\Notifications\Channels\SmsChannel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationDispatcher
{
    /** @var array<string, NotificationChannel> */
    private array $channels;

    public function __construct(
        DatabaseChannel $database,
        MailChannel $mail,
        SmsChannel $sms,
        PushChannel $push,
    ) {
        $this->channels = [
            'database' => $database,
            'mail' => $mail,
            'sms' => $sms,
            'push' => $push,
            // In-app is the database channel (topbar/inbox).
            'in-app' => $database,
        ];
    }

    /**
     * @param  list<string>  $channels
     */
    public function notifyUser(User $user, NotificationMessage $message, array $channels = ['database', 'mail']): void
    {
        $this->dispatch('user', $message, collect([$user]), $channels);
    }

    /**
     * @param  list<string>  $channels
     */
    public function notifyAdmins(NotificationMessage $message, array $channels = ['database', 'mail']): void
    {
        $recipients = $this->adminRecipients($message->permission);
        $this->dispatch('admin', $message, $recipients, $channels);
    }

    /**
     * @param  iterable<User>  $users
     * @param  list<string>  $channels
     */
    public function notifyMany(iterable $users, NotificationMessage $message, array $channels = ['database', 'mail']): void
    {
        $this->dispatch('user', $message, collect($users), $channels);
    }

    /**
     * @param  'user'|'admin'  $audience
     * @param  Collection<int, User>  $recipients
     * @param  list<string>  $channels
     */
    private function dispatch(string $audience, NotificationMessage $message, Collection $recipients, array $channels): void
    {
        foreach ($channels as $channelName) {
            $channel = $this->channels[$channelName] ?? null;
            if (! $channel) {
                continue;
            }

            try {
                // Shared admin inbox does not need per-user recipients for database.
                $channelRecipients = ($audience === 'admin' && in_array($channelName, ['database', 'in-app'], true))
                    ? null
                    : $recipients;

                if ($audience === 'user' && $recipients->isEmpty()) {
                    continue;
                }

                if ($audience === 'admin'
                    && ! in_array($channelName, ['database', 'in-app'], true)
                    && $recipients->isEmpty()
                ) {
                    continue;
                }

                $channel->send($message, $audience, $channelRecipients);
            } catch (Throwable $e) {
                Log::warning('notification.channel_failed', [
                    'channel' => $channelName,
                    'type' => $message->type,
                    'audience' => $audience,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function adminRecipients(?string $permission): Collection
    {
        $query = User::query()->where('is_suspended', false);

        if ($permission) {
            $query->permission($permission);
        } else {
            $query->whereHas('roles');
        }

        return $query->get();
    }
}
