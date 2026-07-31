<?php

namespace App\Services\Communications\Email\Providers;

use App\Models\IntegrationProvider;
use App\Services\Communications\Email\EmailProviderInterface;
use App\Services\Communications\Email\OutgoingEmail;
use App\Services\Communications\Email\SendResult;
use Illuminate\Support\Facades\Http;
use Throwable;

class BrevoApiProvider implements EmailProviderInterface
{
    public function name(): string
    {
        return IntegrationProvider::BREVO;
    }

    public function isAvailable(): bool
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::BREVO);

        return $row->enabled && filled($row->credential('api_key'));
    }

    public function send(OutgoingEmail $email, string $fromName, string $fromEmail, ?string $replyTo = null): SendResult
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::BREVO);
        $apiKey = (string) $row->credential('api_key', '');
        if ($apiKey === '') {
            return SendResult::fail($this->name(), 'Brevo API key is not configured.');
        }

        $payload = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
            ],
            'to' => array_map(fn (array $r) => array_filter([
                'email' => $r['email'],
                'name' => $r['name'] ?? null,
            ]), $email->to),
            'subject' => $email->subject,
        ];

        if ($email->htmlContent) {
            $payload['htmlContent'] = $email->htmlContent;
        }
        if ($email->textContent) {
            $payload['textContent'] = $email->textContent;
        }
        if (! $email->htmlContent && ! $email->textContent) {
            $payload['textContent'] = $email->subject;
        }

        $reply = $replyTo ?: $email->replyTo;
        if ($reply) {
            $payload['replyTo'] = ['email' => $reply];
        }
        if ($email->cc !== []) {
            $payload['cc'] = array_map(fn (array $r) => ['email' => $r['email'], 'name' => $r['name'] ?? null], $email->cc);
        }
        if ($email->bcc !== []) {
            $payload['bcc'] = array_map(fn (array $r) => ['email' => $r['email'], 'name' => $r['name'] ?? null], $email->bcc);
        }
        if ($email->tags !== []) {
            $payload['tags'] = $email->tags;
        }
        if ($email->attachments !== []) {
            $payload['attachment'] = array_map(fn (array $a) => [
                'name' => $a['name'],
                'content' => base64_encode($a['content']),
            ], $email->attachments);
        }
        if ($email->templateId) {
            $payload['templateId'] = $email->templateId;
            $payload['params'] = $email->params;
            unset($payload['htmlContent'], $payload['textContent'], $payload['subject']);
            if ($email->subject) {
                $payload['subject'] = $email->subject;
            }
        }
        if ($email->scheduledAt) {
            $payload['scheduledAt'] = $email->scheduledAt->format('Y-m-d\TH:i:s.v\Z');
        }

        $started = hrtime(true);
        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->timeout(20)->post('https://api.brevo.com/v3/smtp/email', $payload);

            $latency = (int) ((hrtime(true) - $started) / 1_000_000);

            if ($response->successful()) {
                $messageId = $response->json('messageId');

                return SendResult::ok($this->name(), is_string($messageId) ? $messageId : null, $latency);
            }

            $error = $response->json('message') ?: $response->body() ?: 'Brevo API request failed.';

            return SendResult::fail($this->name(), is_string($error) ? $error : 'Brevo API request failed.', $latency);
        } catch (Throwable $e) {
            $latency = (int) ((hrtime(true) - $started) / 1_000_000);

            return SendResult::fail($this->name(), $e->getMessage(), $latency);
        }
    }
}
