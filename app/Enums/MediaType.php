<?php

namespace App\Enums;

enum MediaType: string
{
    case Image = 'image';
    case Video = 'video';
    case Document = 'document';
    case Audio = 'audio';

    public function label(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Video => 'Video',
            self::Document => 'Document',
            self::Audio => 'Audio',
        };
    }

    public function isEnabled(): bool
    {
        return in_array($this->value, config('media.allowed_types', ['image']), true);
    }
}
