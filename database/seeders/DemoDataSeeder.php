<?php

namespace Database\Seeders;

use App\Models\KycSubmission;
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
        $demoUsers = $this->seedDemoUsers();
        $this->seedWallets($demoUsers);
        $this->seedTransactions($demoUsers);
        $this->seedOrders($demoUsers);
        $this->seedSupportTickets($demoUsers);
        $this->seedKycSubmissions($demoUsers);
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
        $subjects = [
            ['subject' => 'Account verification', 'status' => 'open', 'category' => 'kyc'],
            ['subject' => 'Payment issue', 'status' => 'pending', 'category' => 'payment'],
            ['subject' => 'API access', 'status' => 'awaiting_user', 'category' => 'technical'],
            ['subject' => 'Refund request', 'status' => 'resolved', 'category' => 'order'],
            ['subject' => 'Withdrawal delay', 'status' => 'closed', 'category' => 'withdrawal'],
        ];
        foreach ($users as $i => $user) {
            $row = $subjects[$i % count($subjects)];
            SupportTicket::firstOrCreate(
                ['user_id' => $user->id, 'subject' => $row['subject']],
                [
                    'status' => $row['status'],
                    'category' => $row['category'],
                    'body' => 'Demo support ticket body for '.$row['subject'],
                    'priority' => ['normal', 'high', 'urgent'][$i % 3],
                ]
            );
        }
    }

    private function seedKycSubmissions(array $users): void
    {
        $cases = [
            ['status' => 'pending', 'level' => 2],
            ['status' => 'approved', 'level' => 1],
            ['status' => 'rejected', 'level' => 2],
            ['status' => 'pending', 'level' => 3],
        ];
        foreach ($users as $i => $user) {
            $case = $cases[$i % count($cases)];
            KycSubmission::firstOrCreate(
                ['user_id' => $user->id, 'level_requested' => $case['level']],
                [
                    'status' => $case['status'],
                    'level_granted' => $case['status'] === 'approved' ? $case['level'] : null,
                    'documents' => ['id' => 'demo-document.pdf'],
                    'reviewed_at' => in_array($case['status'], ['approved', 'rejected'], true) ? now()->subDays($i + 1) : null,
                ]
            );
        }
    }
}
