<?php

namespace App\Services\Communications\Email;

use Illuminate\Support\Facades\Log;

/**
 * Single app-facing HTML email gateway.
 *
 * All product mail (notifications + transactional OTPs) must go through here so
 * profile fallback and transport behaviour stay identical everywhere.
 * Transport remains EmailService (Brevo → Laravel mail → retry).
 */
class OutboundMail
{
    public function __construct(
        private EmailService $emails,
    ) {}

    /**
     * Send HTML mail. Tries the requested profile first; if that fails and the
     * profile is not already NoReply, retries once with NoReply (the identity
     * proven by Admin → test mail on this install).
     */
    public function sendHtml(
        string $to,
        string $subject,
        string $html,
        EmailProfile $profile = EmailProfile::NoReply,
        ?string $text = null,
        ?string $templateKey = null,
    ): SendResult {
        $to = trim($to);
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return SendResult::fail('outbound', 'Invalid recipient email: '.$to);
        }

        $result = $this->emails->sendMailableHtml(
            to: $to,
            subject: $subject,
            html: $html,
            text: $text,
            profile: $profile,
            templateKey: $templateKey,
        );

        if ($result->success || $profile === EmailProfile::NoReply) {
            return $result;
        }

        Log::warning('outbound_mail.profile_failed_fallback_noreply', [
            'to' => $to,
            'subject' => $subject,
            'profile' => $profile->value,
            'provider' => $result->provider,
            'error' => $result->error,
            'template' => $templateKey,
        ]);

        return $this->emails->sendMailableHtml(
            to: $to,
            subject: $subject,
            html: $html,
            text: $text,
            profile: EmailProfile::NoReply,
            templateKey: $templateKey,
        );
    }

    /**
     * Ops probe only — plain text, NoReply, no HTML layout.
     */
    public function sendTestRaw(string $to, string $subject, string $body): SendResult
    {
        return $this->emails->sendRaw(
            $to,
            $subject,
            $body,
            EmailProfile::NoReply,
        );
    }
}
