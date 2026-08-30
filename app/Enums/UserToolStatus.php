<?php

namespace App\Enums;

enum UserToolStatus: string
{
    case PendingSetup = 'pending_setup';
    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PendingSetup => 'Pending setup',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Expired => 'Expired',
        };
    }
}
