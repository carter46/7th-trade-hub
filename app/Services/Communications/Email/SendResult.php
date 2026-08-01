<?php

namespace App\Services\Communications\Email;

class SendResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $provider,
        public readonly ?string $messageId = null,
        public readonly ?string $error = null,
        public readonly ?int $latencyMs = null,
        public readonly ?int $httpStatus = null,
        public readonly ?string $providerErrorCode = null,
        public readonly ?string $responseBody = null,
        public readonly ?string $requestId = null,
        public readonly ?string $deliveryStatus = null,
    ) {}

    public static function ok(
        string $provider,
        ?string $messageId = null,
        ?int $latencyMs = null,
        ?int $httpStatus = null,
        ?string $responseBody = null,
        ?string $requestId = null,
        ?string $deliveryStatus = 'sent',
    ): self {
        return new self(
            true,
            $provider,
            $messageId,
            null,
            $latencyMs,
            $httpStatus,
            null,
            $responseBody,
            $requestId,
            $deliveryStatus,
        );
    }

    public static function fail(
        string $provider,
        string $error,
        ?int $latencyMs = null,
        ?int $httpStatus = null,
        ?string $providerErrorCode = null,
        ?string $responseBody = null,
        ?string $requestId = null,
        ?string $deliveryStatus = 'failed',
    ): self {
        return new self(
            false,
            $provider,
            null,
            $error,
            $latencyMs,
            $httpStatus,
            $providerErrorCode,
            $responseBody,
            $requestId,
            $deliveryStatus,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toDiagnosticArray(): array
    {
        return [
            'success' => $this->success,
            'provider' => $this->provider,
            'message_id' => $this->messageId,
            'error' => $this->error,
            'latency_ms' => $this->latencyMs,
            'http_status' => $this->httpStatus,
            'provider_error_code' => $this->providerErrorCode,
            'response_body' => $this->responseBody,
            'request_id' => $this->requestId,
            'delivery_status' => $this->deliveryStatus,
        ];
    }
}
