<?php

namespace Database\Seeders\Demo\Support;

class DemoConversationScripts
{
    /**
     * @return array{subject: string, body: string, category: string, replies: list<array{role: string, body: string}>}
     */
    public static function payment(): array
    {
        return [
            'subject' => 'Deposit has not reflected',
            'body' => 'I funded my wallet 2 hours ago but the balance is still unchanged. Reference was in my bank transfer memo.',
            'category' => 'payment',
            'replies' => [
                ['role' => 'admin', 'body' => 'Thanks for reporting this. We are checking your transaction against pending fundings.'],
                ['role' => 'user', 'body' => 'I can upload the transfer receipt if that helps.'],
                ['role' => 'admin', 'body' => 'Receipt received. Locating the matching bank credit now.'],
                ['role' => 'admin', 'body' => 'Funding approved and wallet credited. Please refresh your wallet page.'],
            ],
        ];
    }

    /**
     * @return array{subject: string, body: string, category: string, replies: list<array{role: string, body: string}>}
     */
    public static function kyc(): array
    {
        return [
            'subject' => 'My document was rejected',
            'body' => 'KYC was rejected and I am not sure which document failed the check.',
            'category' => 'kyc',
            'replies' => [
                ['role' => 'admin', 'body' => 'The ID photo was too blurry around the edges. Please upload a clearer document.'],
                ['role' => 'user', 'body' => 'Uploaded a new scan. Can you re-review?'],
                ['role' => 'admin', 'body' => 'New document looks readable. Compliance will re-open the submission shortly.'],
            ],
        ];
    }

    /**
     * @return array{subject: string, body: string, category: string, replies: list<array{role: string, body: string}>}
     */
    public static function orderDelivery(): array
    {
        return [
            'subject' => 'Service not delivered yet',
            'body' => 'I paid for a VPN plan from my wallet, but I have not received the access details.',
            'category' => 'order',
            'replies' => [
                ['role' => 'user', 'body' => 'Still waiting after 48 hours.'],
                ['role' => 'admin', 'body' => 'Thanks for your patience. We are checking the fulfilment status of your order.'],
                ['role' => 'admin', 'body' => 'Access details have been sent to your email and are now visible on the order page.'],
            ],
        ];
    }

    /**
     * @return array{subject: string, body: string, category: string, replies: list<array{role: string, body: string}>}
     */
    public static function withdrawal(): array
    {
        return [
            'subject' => 'Withdrawal delay',
            'body' => 'My withdrawal is still pending after the SLA window.',
            'category' => 'withdrawal',
            'replies' => [
                ['role' => 'admin', 'body' => 'Finance is verifying bank details on the payout batch.'],
                ['role' => 'user', 'body' => 'Bank account ending 2291 is correct.'],
                ['role' => 'admin', 'body' => 'Payout submitted to the bank. You should see it within 24 hours.'],
            ],
        ];
    }

    /**
     * @return array{subject: string, body: string, category: string, replies: list<array{role: string, body: string}>}
     */
    public static function technical(): array
    {
        return [
            'subject' => 'Cannot open wallet page',
            'body' => 'Wallet page spins forever on mobile Safari.',
            'category' => 'technical',
            'replies' => [
                ['role' => 'admin', 'body' => 'Clearing cache and retrying on the latest build usually fixes this. Which iOS version are you on?'],
                ['role' => 'user', 'body' => 'iOS 17.5 — retry worked after logout.'],
            ],
        ];
    }

    /** @return list<array{subject: string, body: string, category: string, replies: list<array{role: string, body: string}>}> */
    public static function pool(): array
    {
        return [
            self::payment(),
            self::kyc(),
            self::orderDelivery(),
            self::withdrawal(),
            self::technical(),
        ];
    }
}
