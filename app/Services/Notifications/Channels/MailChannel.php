<?php

namespace App\Services\Notifications\Channels;

use App\Mail\NotificationMail;
use App\Models\User;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailChannel implements NotificationChannel
{
    public function send(NotificationMessage $message, string $audience, ?iterable $recipients = null): void
    {
        foreach ($recipients ?? [] as $user) {
            if (! $user instanceof User || ! $user->email) {
                continue;
            }

            try {
                $mailable = new NotificationMail($message, $user);
                if (config('queue.default') === 'sync') {
                    Mail::to($user->email)->send($mailable);
                } else {
                    Mail::to($user->email)->queue($mailable);
                }
            } catch (Throwable $e) {
                Log::warning('notification.mail_failed', [
                    'user_id' => $user->id,
                    'type' => $message->type,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
