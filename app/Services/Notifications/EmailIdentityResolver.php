<?php

namespace App\Services\Notifications;

use App\Models\EmailIdentity;
use App\Services\Communications\Email\EmailProfile;

class EmailIdentityResolver
{
    public function resolveProfileForType(string $type): EmailProfile
    {
        $type = strtolower(trim($type));

        return match (true) {
            str_starts_with($type, 'ticket.') => EmailProfile::Support,
            // Sales: orders, tools, marketplace order/escrow (dotted + legacy undotted)
            str_starts_with($type, 'order.'),
            str_starts_with($type, 'tool.'),
            $type === 'order' => EmailProfile::Sales,
            str_starts_with($type, 'user.') => EmailProfile::General,
            str_starts_with($type, 'security.'),
            str_starts_with($type, 'auth.'),
            str_starts_with($type, 'email.delivery_failed') => EmailProfile::Security,
            str_starts_with($type, 'wallet.'),
            str_starts_with($type, 'crypto.'),
            str_starts_with($type, 'treasury.'),
            str_starts_with($type, 'payment.'),
            str_starts_with($type, 'escrow.'),
            $type === 'wallet' => EmailProfile::Billing,
            // Listing / marketplace notices + default transactional
            str_starts_with($type, 'listing.'),
            $type === 'listing',
            $type === 'message',
            $type === 'review',
            str_starts_with($type, 'verification.'),
            str_starts_with($type, 'password.'),
            str_starts_with($type, 'bank.'),
            str_starts_with($type, 'withdrawal.') => EmailProfile::NoReply,
            default => EmailProfile::NoReply,
        };
    }

    public function notifyToEmailForProfile(EmailProfile $profile): ?string
    {
        $identity = EmailIdentity::forProfile($profile->value);

        $email = $identity?->notify_to_email ?: null;

        return filled($email) ? (string) $email : null;
    }
}
