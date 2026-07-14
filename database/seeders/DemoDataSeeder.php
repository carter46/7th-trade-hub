<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedListings();
        $demoUsers = $this->seedDemoUsers();
        $this->seedWallets($demoUsers);
        $this->seedTransactions($demoUsers);
        $this->seedOrders($demoUsers);
        $this->seedSupportTickets($demoUsers);
    }

    private function seedListings(): void
    {
        $listings = [
            [
                'title' => 'E-commerce API Dev',
                'slug' => 'ecommerce-api-dev',
                'description' => 'Custom backend integration for storefronts.',
                'price' => 450.00,
                'category' => 'code',
                'icon_class' => 'code bg-blue-600/20 text-blue-500',
            ],
            [
                'title' => 'Logo Branding Kit',
                'slug' => 'logo-branding-kit',
                'description' => 'Professional vector logos and guidelines.',
                'price' => 125.00,
                'category' => 'image',
                'icon_class' => 'image bg-pink-600/20 text-pink-500',
            ],
            [
                'title' => 'Legal Sales Contract',
                'slug' => 'legal-sales-contract',
                'description' => 'Verified template for digital trade.',
                'price' => 49.00,
                'category' => 'document',
                'icon_class' => 'description bg-green-600/20 text-green-500',
            ],
        ];

        foreach ($listings as $item) {
            Listing::firstOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, ['is_active' => true, 'status' => 'published'])
            );
        }
    }

    private function seedDemoUsers(): array
    {
        $demos = [
            ['name' => 'Jordan Smith', 'username' => 'jordansmith', 'email' => 'jordan@example.com'],
            ['name' => 'Sarah Chen', 'username' => 'sarahchen', 'email' => 'sarah@example.com'],
            ['name' => 'Mike Ross', 'username' => 'mikeross', 'email' => 'mike@example.com'],
            ['name' => 'Alex Thompson', 'username' => 'alexthompson', 'email' => 'alex@example.com'],
        ];

        $users = [];
        foreach ($demos as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['name'],
                    'username' => $d['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'kyc_level' => 1,
                ]
            );
            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }
            $users[] = $user;
        }

        return $users;
    }

    private function seedWallets(array $users): void
    {
        $defaults = [
            ['balance' => 42560.80, 'locked_balance' => 0, 'currency' => 'NGN', 'status' => 'active'],
            ['balance' => 18500.00, 'locked_balance' => 0, 'currency' => 'NGN', 'status' => 'active'],
            ['balance' => 9200.00, 'locked_balance' => 0, 'currency' => 'NGN', 'status' => 'active'],
            ['balance' => 67800.00, 'locked_balance' => 0, 'currency' => 'NGN', 'status' => 'active'],
        ];

        foreach (array_values($users) as $i => $user) {
            $data = $defaults[$i % count($defaults)] ?? $defaults[0];
            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                $data
            );
        }
    }

    private function seedTransactions(array $users): void
    {
        $byEmail = collect($users)->keyBy('email');

        $adminTableRows = [
            ['ref' => 'TX92841029', 'email' => 'jordan@example.com', 'label' => 'BTC', 'amount' => 0.421, 'currency' => 'BTC', 'asset_type' => 'BTC', 'status' => 'pending'],
            ['ref' => 'TX92841030', 'email' => 'sarah@example.com', 'label' => 'ETH', 'amount' => 2.5, 'currency' => 'ETH', 'asset_type' => 'ETH', 'status' => 'completed'],
            ['ref' => 'TX92841031', 'email' => 'mike@example.com', 'label' => 'BTC', 'amount' => 1.22, 'currency' => 'BTC', 'asset_type' => 'BTC', 'status' => 'failed'],
        ];

        foreach ($adminTableRows as $row) {
            $user = $byEmail->get($row['email']);
            if ($user && ! Transaction::where('reference', $row['ref'])->exists()) {
                Transaction::create([
                    'user_id' => $user->id,
                    'reference' => $row['ref'],
                    'type' => 'crypto_purchase',
                    'label' => $row['label'],
                    'amount' => $row['amount'],
                    'currency' => $row['currency'],
                    'asset_type' => $row['asset_type'],
                    'status' => $row['status'],
                ]);
            }
        }

        $userDashboardRows = [
            ['ref' => 'TRD-90421', 'label' => 'Bitcoin Purchase', 'amount' => 12450.00, 'status' => 'completed'],
            ['ref' => 'TRD-88210', 'label' => 'Hosting Bundle', 'amount' => 240.00, 'status' => 'processing'],
        ];

        foreach ($users as $user) {
            foreach ($userDashboardRows as $row) {
                $ref = $row['ref'] . '-' . $user->id;
                if (! Transaction::where('reference', $ref)->exists()) {
                    Transaction::create([
                        'user_id' => $user->id,
                        'reference' => $ref,
                        'type' => str_contains($row['label'], 'Bitcoin') ? 'crypto_purchase' : 'service',
                        'label' => $row['label'],
                        'amount' => $row['amount'],
                        'currency' => 'USD',
                        'asset_type' => null,
                        'status' => $row['status'],
                    ]);
                }
            }
        }
    }

    private function seedOrders(array $users): void
    {
        $listings = Listing::all();
        if ($listings->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            foreach ($listings->take(2) as $i => $listing) {
                $ref = 'ORD-' . $user->id . '-' . ($i + 1);
                Order::firstOrCreate(
                    ['reference' => $ref],
                    [
                        'user_id' => $user->id,
                        'listing_id' => $listing->id,
                        'reference' => $ref,
                        'amount' => $listing->price,
                        'status' => ['pending', 'processing', 'completed'][$i % 3],
                    ]
                );
            }
        }
    }

    private function seedSupportTickets(array $users): void
    {
        $subjects = ['Account verification', 'Payment issue', 'API access', 'Refund request'];
        foreach ($users as $i => $user) {
            SupportTicket::firstOrCreate(
                ['user_id' => $user->id, 'subject' => $subjects[$i % count($subjects)]],
                ['status' => ['open', 'in_progress', 'closed'][$i % 3]]
            );
        }
    }
}
