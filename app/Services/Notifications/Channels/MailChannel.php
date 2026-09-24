<?php

namespace App\Services\Notifications\Channels;

use App\Models\User;
use App\Services\Communications\Email\OutboundMail;
use App\Services\Communications\Email\SendResult;
use App\Services\Notifications\EmailIdentityResolver;
use App\Services\Notifications\NotificationDedupeService;
use App\Services\Notifications\NotificationDeliveryTracer;
use App\Services\Notifications\NotificationEmailRenderer;
use App\Services\Notifications\NotificationMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Canonical notification mail path: resolve profile → render layout → OutboundMail.
 */
class MailChannel implements NotificationChannel
{
    public function __construct(
        private OutboundMail $outbound,
        private EmailIdentityResolver $identityResolver,
        private NotificationDedupeService $dedupe,
        private NotificationDeliveryTracer $tracer,
        private NotificationEmailRenderer $renderer,
    ) {}

    public function send(NotificationMessage $message, string $audience, ?iterable $recipients = null): void
    {
        $this->sendWithResult($message, $audience, $recipients);
    }

    /**
     * Same as send(), but returns the last recipient's SendResult (for admin UX).
     */
    public function sendWithResult(NotificationMessage $message, string $audience, ?iterable $recipients = null): ?SendResult
    {
        $profile = $this->identityResolver->resolveProfileForType($message->type);

        if ($this->dedupe->shouldSkip($message->type, $message->dedupeKey, 'mail')) {
            $this->tracer->record(
                notificationType: $message->type,
                channel: 'mail',
                status: 'deduped',
                profile: $profile->value,
                dedupeKey: $message->dedupeKey,
                event: $message->meta['event'] ?? null,
            );

            return SendResult::fail('dedupe', 'Skipped: duplicate mail claim for '.$message->type);
        }

        if (! $this->dedupe->tryClaim($message->type, $message->dedupeKey, 'mail')) {
            $this->tracer->record(
                notificationType: $message->type,
                channel: 'mail',
                status: 'deduped',
                profile: $profile->value,
                dedupeKey: $message->dedupeKey,
                event: $message->meta['event'] ?? null,
            );

            return SendResult::fail('dedupe', 'Skipped: could not claim mail dedupe for '.$message->type);
        }

        $emails = $this->resolveRecipientEmails($audience, $recipients, $profile);

        if ($emails->isEmpty()) {
            $this->tracer->record(
                notificationType: $message->type,
                channel: 'mail',
                status: 'skipped',
                profile: $profile->value,
                dedupeKey: $message->dedupeKey,
                failureReason: 'No recipients',
            );

            return SendResult::fail('outbound', 'No mail recipients');
        }

        $context = $message->meta['email_context'] ?? [];
        $sentAny = false;
        $lastResult = null;

        foreach ($emails as $entry) {
            $to = $entry['email'];
            $user = $entry['user'];

            try {
                $html = $audience === 'admin'
                    ? $this->renderer->renderAdmin($message, $user ?? new User(['name' => 'Admin', 'email' => $to]), $context)
                    : $this->renderer->renderUser($message, $user ?? new User(['name' => 'User', 'email' => $to]), $context);

                $result = $this->outbound->sendHtml(
                    to: $to,
                    subject: $message->emailSubject ?: $message->title,
                    html: $html,
                    profile: $profile,
                    templateKey: 'notification',
                );
                $lastResult = $result;

                if ($result->success) {
                    $sentAny = true;
                    $this->tracer->record(
                        notificationType: $message->type,
                        channel: 'mail',
                        status: 'sent',
                        profile: $profile->value,
                        recipient: $to,
                        dedupeKey: $message->dedupeKey,
                        event: $message->meta['event'] ?? null,
                    );
                } else {
                    $this->tracer->record(
                        notificationType: $message->type,
                        channel: 'mail',
                        status: 'failed',
                        profile: $profile->value,
                        recipient: $to,
                        dedupeKey: $message->dedupeKey,
                        failureReason: $result->error,
                        event: $message->meta['event'] ?? null,
                    );
                }
            } catch (Throwable $e) {
                Log::warning('notification.mail_failed', [
                    'email' => $to,
                    'type' => $message->type,
                    'error' => $e->getMessage(),
                ]);

                $lastResult = SendResult::fail('outbound', $e->getMessage());

                $this->tracer->record(
                    notificationType: $message->type,
                    channel: 'mail',
                    status: 'failed',
                    profile: $profile->value,
                    recipient: $to,
                    dedupeKey: $message->dedupeKey,
                    failureReason: $e->getMessage(),
                    event: $message->meta['event'] ?? null,
                );
            }
        }

        // Do not block retries for 24h when the provider never accepted the message.
        if (! $sentAny) {
            $this->dedupe->release($message->type, $message->dedupeKey, 'mail');
        }

        return $lastResult;
    }

    /**
     * @return Collection<int, array{email: string, user: ?User}>
     */
    private function resolveRecipientEmails(string $audience, ?iterable $recipients, \App\Services\Communications\Email\EmailProfile $profile): Collection
    {
        $rows = collect();

        foreach ($recipients ?? [] as $user) {
            if ($user instanceof User && $user->email) {
                $rows->push(['email' => $user->email, 'user' => $user]);
            }
        }

        if ($audience === 'admin') {
            $inbox = $this->identityResolver->notifyToEmailForProfile($profile);
            if ($inbox && ! $rows->contains(fn ($row) => $row['email'] === $inbox)) {
                $rows->push(['email' => $inbox, 'user' => null]);
            }
        }

        return $rows->unique('email')->values();
    }
}
