<?php

namespace App\Services\Communications\Email;

interface EmailProviderInterface
{
    public function name(): string;

    public function isAvailable(): bool;

    public function send(OutgoingEmail $email, string $fromName, string $fromEmail, ?string $replyTo = null): SendResult;
}
