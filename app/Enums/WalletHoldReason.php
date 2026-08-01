<?php

namespace App\Enums;

enum WalletHoldReason: string
{
    case Escrow = 'escrow';
    case Withdrawal = 'withdrawal';
    case Listing = 'listing';
    case Compliance = 'compliance';
}
