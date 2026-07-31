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
    ) {}

    public static function ok(string $provider, ?string $messageId = null, ?int $latencyMs = null): self
    {
        return new self(true, $provider, $messageId, null, $latencyMs);
    }

    public static function fail(string $provider, string $error, ?int $latencyMs = null): self
    {
        return new self(false, $provider, null, $error, $latencyMs);
    }
}
