<?php

namespace App\Enums;

enum UserToolStatus: string
{
    case PendingSetup = 'pending_setup';
    case Active = 'active';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
    case Inactive = 'inactive';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PendingSetup => 'Pending setup',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Cancelled => 'Cancelled',
            self::Inactive => 'Inactive',
            self::Expired => 'Expired',
        };
    }

    /**
     * Reasons an admin may pick when shutting down a live website.
     *
     * @return list<self>
     */
    public static function adminShutdownReasons(): array
    {
        return [
            self::Suspended,
            self::Cancelled,
            self::Inactive,
            self::Expired,
        ];
    }

    /**
     * Non-expired admin-hold statuses (preserved by the expiry job / effectiveStatus).
     * Expired remains a normal terminal status and is also selectable as a shutdown reason.
     */
    public function isAdminHoldStatus(): bool
    {
        return in_array($this, [self::Suspended, self::Cancelled, self::Inactive], true);
    }

    public function isAdminShutdown(): bool
    {
        return $this->isAdminHoldStatus();
    }

    /**
     * Status string sent on Protocol v1 subscription push/poll.
     * Merchants must branch UI on these values (see MERCHANT-GUIDE shutdown messaging).
     */
    public function protocolValue(): string
    {
        return $this->value;
    }
}
