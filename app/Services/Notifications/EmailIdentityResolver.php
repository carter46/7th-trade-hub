<?php

namespace App\Services\Notifications;

use App\Models\EmailIdentity;
use App\Services\Communications\Email\EmailProfile;

/**
 * Maps notification types → email identity profile.
 *
 * For admin audience mail, the profile's notify_to_email is the shared inbox
 * (plus staff users matching the message permission).
 * For user audience mail, the profile is the From identity.
 *
 * Intended inbox mapping (configure notify_to on each identity in Email settings):
 * - general  → info / platform ops (signups, domain fulfillment, listings)
 * - sales    → orders, tools, marketplace purchases
 * - support  → tickets
 * - billing  → wallet, crypto, payments, escrow, bank-transfer proofs
 * - security → auth / security alerts (and direct OutboundMail Security OTPs)
 * - noreply  → pure transactional fallbacks
 */
class EmailIdentityResolver
{
    public function resolveProfileForType(string $type): EmailProfile
    {
        $type = strtolower(trim($type));

        return match (true) {
            // Support
            str_starts_with($type, 'ticket.') => EmailProfile::Support,

            // General / info — platform ops (not Sales)
            str_starts_with($type, 'domain.'),
            str_starts_with($type, 'user.'),
            str_starts_with($type, 'listing.'),
            $type === 'listing',
            $type === 'order.domain_replacement_requested' => EmailProfile::General,

            // Billing — money movement + payment ops (before order.* catch-all)
            str_starts_with($type, 'wallet.'),
            str_starts_with($type, 'crypto.'),
            str_starts_with($type, 'treasury.'),
            str_starts_with($type, 'payment.'),
            str_starts_with($type, 'escrow.'),
            str_starts_with($type, 'withdrawal.'),
            $type === 'wallet',
            $type === 'order.manual_bank_transfer_proof',
            $type === 'order.manual_bank_transfer_failed' => EmailProfile::Billing,

            // Sales — commerce
            str_starts_with($type, 'order.'),
            str_starts_with($type, 'tool.'),
            $type === 'order' => EmailProfile::Sales,

            // Security
            str_starts_with($type, 'security.'),
            str_starts_with($type, 'auth.'),
            str_starts_with($type, 'email.delivery_failed') => EmailProfile::Security,

            // NoReply — lightweight transactional
            $type === 'message',
            $type === 'review',
            str_starts_with($type, 'verification.'),
            str_starts_with($type, 'password.'),
            str_starts_with($type, 'bank.') => EmailProfile::NoReply,

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
