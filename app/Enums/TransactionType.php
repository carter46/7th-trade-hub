<?php

namespace App\Enums;

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
}
