<?php

namespace App\Enums;

/**
 * EscrowLock, EscrowRelease, Refund, PlatformFee, ListingHold and ListingHoldRelease are
 * legacy-only: the peer marketplace and escrow were removed, nothing may create these any
 * more, and the cases exist so historical ledger rows still load and display.
 */
enum TransactionType: string
{
    case Funding = 'funding';
    case Withdrawal = 'withdrawal';
    case EscrowLock = 'escrow_lock';
    case EscrowRelease = 'escrow_release';
    case Refund = 'refund';
    case PlatformFee = 'platform_fee';
    case Purchase = 'purchase';
    case AdminAdjustment = 'admin_adjustment';
    case Reversal = 'reversal';
    case WithdrawalUnlock = 'withdrawal_unlock';
    case WithdrawalHold = 'withdrawal_hold';
    case ListingHold = 'listing_hold';
    case ListingHoldRelease = 'listing_hold_release';

    public function label(): string
    {
        return match ($this) {
            self::Funding => 'Deposit',
            self::Withdrawal => 'Withdrawal',
            self::Purchase => 'Purchase',
            self::AdminAdjustment => 'Admin adjustment',
            self::Reversal => 'Reversal',
            self::WithdrawalUnlock => 'Withdrawal returned',
            self::WithdrawalHold => 'Withdrawal hold',
            self::EscrowLock => 'Legacy purchase hold',
            self::EscrowRelease => 'Legacy sale payout',
            self::Refund => 'Legacy refund',
            self::PlatformFee => 'Legacy platform fee',
            self::ListingHold => 'Legacy hold',
            self::ListingHoldRelease => 'Legacy hold release',
        };
    }

    public static function labelFor(?string $type): string
    {
        $case = $type !== null ? self::tryFrom($type) : null;

        return $case?->label() ?? ucfirst(str_replace('_', ' ', (string) $type));
    }
}
