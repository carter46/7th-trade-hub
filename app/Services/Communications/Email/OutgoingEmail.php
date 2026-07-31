<?php

namespace App\Services\Communications\Email;

class OutgoingEmail
{
    /**
     * @param  list<array{email: string, name?: string|null}>  $to
     * @param  list<array{email: string, name?: string|null}>  $cc
     * @param  list<array{email: string, name?: string|null}>  $bcc
     * @param  list<array{name: string, content: string, type?: string}>  $attachments
     * @param  list<string>  $tags
     * @param  array<string, mixed>  $params
     */
    public function __construct(
        public array $to,
        public string $subject,
        public ?string $htmlContent = null,
        public ?string $textContent = null,
        public ?string $templateKey = null,
        public EmailProfile $profile = EmailProfile::NoReply,
        public ?string $replyTo = null,
        public array $cc = [],
        public array $bcc = [],
        public array $attachments = [],
        public array $tags = [],
        public ?int $templateId = null,
        public array $params = [],
        public ?\DateTimeInterface $scheduledAt = null,
    ) {}
}
