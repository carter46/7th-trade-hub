<?php

namespace App\Services\Notifications;

class NotificationMessage
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $body = null,
        public readonly ?string $actionUrl = null,
        public readonly array $meta = [],
        public readonly ?string $emailSubject = null,
        public readonly ?string $emailView = null,
        public readonly string $priority = 'normal',
        public readonly ?string $permission = null,
        public readonly ?string $dedupeKey = null,
    ) {}
}
