<?php

namespace App\Services\Communications\Email\Providers;

use App\Models\IntegrationProvider;
use App\Services\Communications\Email\EmailProviderInterface;
use App\Services\Communications\Email\OutgoingEmail;
use App\Services\Communications\Email\SendResult;
use Illuminate\Http\Client\Response;
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

        $payload = $this->buildPayload($email, $fromName, $fromEmail, $replyTo);

        $started = hrtime(true);
        try {
            $response = Http::withHeaders($this->headers($apiKey))
                ->timeout(20)
                ->post('https://api.brevo.com/v3/smtp/email', $payload);

            $latency = (int) ((hrtime(true) - $started) / 1_000_000);

            return $this->resultFromResponse($response, $latency);
        } catch (Throwable $e) {
            $latency = (int) ((hrtime(true) - $started) / 1_000_000);

            return SendResult::fail(
                $this->name(),
                $e->getMessage(),
                $latency,
                null,
                'exception',
                mb_substr($e->getMessage(), 0, 2000),
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $apiKey): array
    {
        return [
            'api-key' => $apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
    }

    private function resultFromResponse(Response $response, int $latency): SendResult
    {
        $status = $response->status();
        $json = $response->json();
        $body = is_array($json) ? $json : null;
        $sanitized = $this->sanitizeBody($body ?? $response->body());
        $requestId = $this->extractRequestId($response, $body);
        $code = is_array($body) ? ($body['code'] ?? null) : null;
        $code = is_string($code) || is_numeric($code) ? (string) $code : null;

        if ($response->successful()) {
            $messageId = is_array($body) ? ($body['messageId'] ?? null) : null;

            return SendResult::ok(
                $this->name(),
                is_string($messageId) ? $messageId : null,
                $latency,
                $status,
                $sanitized,
                $requestId,
                'sent',
            );
        }

        $error = is_array($body)
            ? (string) ($body['message'] ?? $body['error'] ?? 'Brevo rejected this email.')
            : (string) ($response->body() ?: 'Brevo API request failed.');

        return SendResult::fail(
            $this->name(),
            $error,
            $latency,
            $status,
            $code,
            $sanitized,
            $requestId,
            'failed',
        );
    }

    /**
     * @param  array<string, mixed>|string|null  $body
     */
    private function sanitizeBody(array|string|null $body): ?string
    {
        if ($body === null) {
            return null;
        }

        if (is_array($body)) {
            unset($body['api-key'], $body['api_key'], $body['key']);
            $encoded = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $encoded === false ? null : mb_substr($encoded, 0, 4000);
        }

        $text = (string) $body;
        $text = preg_replace('/xkeysib-[a-zA-Z0-9\-]+/', '[redacted-api-key]', $text) ?? $text;

        return mb_substr($text, 0, 4000);
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    private function extractRequestId(Response $response, ?array $body): ?string
    {
        foreach (['x-sib-request-id', 'x-request-id', 'x-brevo-request-id'] as $header) {
            $value = $response->header($header);
            if (filled($value)) {
                return (string) $value;
            }
        }

        if (is_array($body)) {
            foreach (['messageId', 'requestId', 'id'] as $key) {
                if (isset($body[$key]) && (is_string($body[$key]) || is_numeric($body[$key]))) {
                    return (string) $body[$key];
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(OutgoingEmail $email, string $fromName, string $fromEmail, ?string $replyTo): array
    {
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

        return $payload;
    }
}
