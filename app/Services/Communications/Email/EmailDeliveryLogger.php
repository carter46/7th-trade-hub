<?php

namespace App\Services\Communications\Email;

use App\Models\EmailDeliveryAttempt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class EmailDeliveryLogger
{
    public function newCorrelationId(): string
    {
        return (string) Str::uuid();
    }

    public function log(
        string $correlationId,
        OutgoingEmail $email,
        SendResult $result,
        int $attemptNumber,
        bool $isFallback = false,
        ?string $deliveryStatus = null,
    ): void {
        try {
            if (! Schema::hasTable('email_delivery_attempts')) {
                return;
            }

            $recipient = $email->to[0]['email'] ?? null;
            EmailDeliveryAttempt::query()->create([
                'correlation_id' => $correlationId,
                'provider' => $result->provider,
                'success' => $result->success,
                'recipient' => $recipient ? mb_substr($recipient, 0, 255) : null,
                'subject' => mb_substr($email->subject, 0, 255),
                'template_key' => $email->templateKey ? mb_substr($email->templateKey, 0, 120) : null,
                'purpose' => mb_substr($email->profile->value.($email->templateKey ? ':'.$email->templateKey : ''), 0, 80),
                'http_status' => $result->httpStatus,
                'provider_error_code' => $result->providerErrorCode ? mb_substr($result->providerErrorCode, 0, 120) : null,
                'error_message' => $result->error ? mb_substr($result->error, 0, 2000) : null,
                'response_body' => $result->responseBody ? mb_substr($result->responseBody, 0, 4000) : null,
                'message_id' => $result->messageId ? mb_substr($result->messageId, 0, 255) : null,
                'request_id' => $result->requestId ? mb_substr($result->requestId, 0, 255) : null,
                'latency_ms' => $result->latencyMs,
                'delivery_status' => $deliveryStatus ?? $result->deliveryStatus,
                'is_fallback' => $isFallback,
                'attempt_number' => $attemptNumber,
                'meta' => [
                    'tags' => $email->tags,
                    'to_count' => count($email->to),
                ],
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('email.delivery_attempt_log_failed', [
                'error' => $e->getMessage(),
                'provider' => $result->provider,
                'correlation_id' => $correlationId,
            ]);
        }
    }
}
