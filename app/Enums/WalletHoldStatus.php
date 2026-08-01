<?php

namespace App\Enums;

enum WalletHoldStatus: string
{
    case Active = 'active';
    case Released = 'released';
    case Consumed = 'consumed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
