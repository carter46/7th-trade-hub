<?php

namespace App\Services\Communications\Email;

enum EmailProfile: string
{
    case General = 'general';
    case Support = 'support';
    case Sales = 'sales';
    case Security = 'security';
    case Billing = 'billing';
    case NoReply = 'noreply';
}
