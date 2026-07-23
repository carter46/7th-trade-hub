<?php

namespace Database\Seeders\Demo\Support;

class DemoPersonaCatalog
{
    /**
     * Named member personas. months_ago = account age.
     * journey events are relative month offsets from "now".
     *
     * @return list<array<string, mixed>>
     */
    public static function named(): array
    {
        return [
            [
                'key' => 'alice',
                'name' => 'Investor Alice',
                'email' => 'alice@example.com',
                'username' => 'aliceinvestor',
                'months_ago' => 8,
                'kyc_level' => 2,
                'kyc_status' => 'approved',
                'role' => 'buyer',
                'bio' => 'Active marketplace buyer and wallet funder.',
            ],
            [
                'key' => 'michael',
                'name' => 'Trader Michael',
                'email' => 'michael@example.com',
                'username' => 'trader_michael',
                'months_ago' => 6,
                'kyc_level' => 0,
                'kyc_status' => 'pending',
                'role' => 'seller',
                'bio' => 'Heavy marketplace seller with escrow history.',
            ],
            [
                'key' => 'sarah',
                'name' => 'Designer Sarah',
                'email' => 'sarah.design@example.com',
                'username' => 'sarahdesign',
                'months_ago' => 7,
                'kyc_level' => 2,
                'kyc_status' => 'approved',
                'role' => 'seller',
                'bio' => 'Sells templates and completed payouts.',
            ],
            [
                'key' => 'john',
                'name' => 'John Rejected',
                'email' => 'john@example.com',
                'username' => 'johnr',
                'months_ago' => 5,
                'kyc_level' => 0,
                'kyc_status' => 'rejected',
                'role' => 'inactive',
                'is_suspended' => false,
                'bio' => 'Rejected KYC with one appeal.',
            ],
            [
                'key' => 'emily',
                'name' => 'Emily New',
                'email' => 'emily@example.com',
                'username' => 'emilynew',
                'months_ago' => 0,
                'kyc_level' => 0,
                'kyc_status' => 'none',
                'role' => 'empty',
                'bio' => 'Brand new user — empty dashboards by design.',
            ],
        ];
    }

    /**
     * Filler members for volume + distribution.
     *
     * @return list<array<string, mixed>>
     */
    public static function fillers(): array
    {
        $rows = [];
        $statuses = [
            'approved', 'approved', 'approved', 'approved',
            'pending', 'pending',
            'rejected',
            'pending', 'pending',
            'pending',
            'approved',
            'approved',
            'rejected',
            'rejected',
            'approved',
        ];

        foreach ($statuses as $i => $status) {
            $n = $i + 1;
            $rows[] = [
                'key' => 'filler'.$n,
                'name' => 'Member '.$n,
                'email' => "member{$n}@example.com",
                'username' => 'member'.$n,
                'months_ago' => max(0, 7 - (int) floor($i / 2)),
                'kyc_level' => $status === 'approved' ? (($i % 2) + 1) : 0,
                'kyc_status' => $status,
                'role' => $i % 3 === 0 ? 'seller' : 'buyer',
                'bio' => 'Demo filler persona '.$n,
            ];
        }

        return $rows;
    }

    /** @return list<array<string, mixed>> */
    public static function allMembers(): array
    {
        return array_merge(self::named(), self::fillers());
    }
}
