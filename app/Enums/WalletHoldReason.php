<?php

namespace App\Enums;

/**
 * Escrow, Listing and ListingHold are legacy-only: escrow and the peer marketplace were
 * removed, nothing may create these any more, and the cases exist so historical hold rows
 * still load.
 */
enum WalletHoldReason: string
{
    case Escrow = 'escrow';
    case Withdrawal = 'withdrawal';
    case Listing = 'listing';
    case ListingHold = 'listing_hold';
    case Compliance = 'compliance';
}
