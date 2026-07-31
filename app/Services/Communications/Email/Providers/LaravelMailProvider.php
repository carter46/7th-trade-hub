<?php

namespace App\Services\Communications\Email\Providers;

use App\Models\IntegrationProvider;
use App\Services\Communications\Email\EmailProviderInterface;
use App\Services\Communications\Email\OutgoingEmail;
use App\Services\Communications\Email\SendResult;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LaravelMailProvider implements EmailProviderInterface
{
    public function name(): string
    {
        return IntegrationProvider::LARAVEL_MAIL;
    }

    public function isAvailable(): bool
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::LARAVEL_MAIL);

        // Must be explicitly enabled in integration_providers (admin toggle).
        return (bool) $row->enabled;
    }

    public function send(OutgoingEmail $email, string $fromName, string $fromEmail, ?string $replyTo = null): SendResult
    {
        $this->applyDbMailConfig();

        $started = hrtime(true);
        try {
            $to = array_map(fn (array $r) => $r['email'], $email->to);
            Mail::mailer($this->resolveMailer())->send([], [], function (Message $message) use ($email, $fromName, $fromEmail, $replyTo, $to) {
                $message->to($to)
                    ->from($fromEmail, $fromName)
                    ->subject($email->subject);

                $html = $email->htmlContent;
                $text = $email->textContent;
                if ($html) {
                    $message->html($html);
                }
                if ($text) {
                    $message->text($text);
                }
                if (! $html && ! $text) {
                    $message->text($email->subject);
                }

                $reply = $replyTo ?: $email->replyTo;
                if ($reply) {
                    $message->replyTo($reply);
                }
                foreach ($email->cc as $cc) {
                    $message->cc($cc['email'], $cc['name'] ?? null);
                }
                foreach ($email->bcc as $bcc) {
                    $message->bcc($bcc['email'], $bcc['name'] ?? null);
                }
                foreach ($email->attachments as $attachment) {
                    $message->attachData($attachment['content'], $attachment['name'], [
                        'mime' => $attachment['type'] ?? 'application/octet-stream',
                    ]);
                }
            });

            $latency = (int) ((hrtime(true) - $started) / 1_000_000);

            return SendResult::ok($this->name(), null, $latency);
        } catch (Throwable $e) {
            $latency = (int) ((hrtime(true) - $started) / 1_000_000);

            return SendResult::fail($this->name(), $e->getMessage(), $latency);
        }
    }

    private function applyDbMailConfig(): void
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::LARAVEL_MAIL);
        if (! $row->enabled) {
            return;
        }

        $mailer = (string) $row->credential('mailer', 'smtp');
        Config::set('mail.default', $mailer === 'sendmail' ? 'sendmail' : 'smtp');

        if ($mailer === 'smtp' || $mailer === '') {
            Config::set('mail.mailers.smtp.host', $row->credential('host', config('mail.mailers.smtp.host')));
            Config::set('mail.mailers.smtp.port', $row->credential('port', config('mail.mailers.smtp.port')));
            Config::set('mail.mailers.smtp.encryption', $row->credential('encryption', config('mail.mailers.smtp.encryption')));
            Config::set('mail.mailers.smtp.username', $row->credential('username', config('mail.mailers.smtp.username')));
            Config::set('mail.mailers.smtp.password', $row->credential('password', config('mail.mailers.smtp.password')));
        }
        if ($mailer === 'sendmail' && $row->credential('sendmail_path')) {
            Config::set('mail.mailers.sendmail.path', $row->credential('sendmail_path'));
        }
    }

    private function resolveMailer(): string
    {
        return (string) config('mail.default', 'smtp');
    }
}
