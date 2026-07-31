<?php

namespace App\Services\Notifications\Channels;

use App\Models\User;
use App\Services\Communications\Email\EmailProfile;
use App\Services\Communications\Email\EmailService;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class MailChannel implements NotificationChannel
{
    public function __construct(
        private EmailService $emails,
    ) {}

    public function send(NotificationMessage $message, string $audience, ?iterable $recipients = null): void
    {
        foreach ($recipients ?? [] as $user) {
            if (! $user instanceof User || ! $user->email) {
                continue;
            }

            try {
                $view = $message->emailView ?: 'emails.notification';
                $html = view($view, [
                    'message' => $message,
                    'notifiable' => $user,
                ])->render();

                $this->emails->sendMailableHtml(
                    to: $user->email,
                    subject: $message->emailSubject ?: $message->title,
                    html: $html,
                    profile: $this->profileFor($message),
                    templateKey: 'notification',
                );
            } catch (Throwable $e) {
                Log::warning('notification.mail_failed', [
                    'user_id' => $user->id,
                    'type' => $message->type,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function profileFor(NotificationMessage $message): EmailProfile
    {
        $type = strtolower((string) $message->type);

        return match (true) {
            str_starts_with($type, 'ticket.') => EmailProfile::Support,
            str_starts_with($type, 'wallet.'),
            str_starts_with($type, 'escrow.'),
            str_starts_with($type, 'billing.') => EmailProfile::Billing,
            str_starts_with($type, 'security.'),
            str_contains($type, 'password'),
            str_contains($type, 'otp') => EmailProfile::Security,
            str_starts_with($type, 'listing.') => EmailProfile::NoReply,
            default => EmailProfile::NoReply,
        };
    }
}
