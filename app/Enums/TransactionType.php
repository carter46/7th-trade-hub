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
    case AdminAdjustment = 'admin_adjustment';
    case Reversal = 'reversal';
    case WithdrawalUnlock = 'withdrawal_unlock';
}
