<?php

namespace Database\Seeders\Demo;

use App\Enums\TransactionType;
use App\Enums\WalletHoldStatus;
use App\Models\AnalyticsKpiSnapshot;
use App\Models\KycSubmission;
use App\Models\Order;
use App\Models\PlatformProduct;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Wallet;
use App\Models\WalletHold;
use App\Modules\Wallet\Services\WalletService;
use App\Support\Demo\DemoGate;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoPlatformSeeder extends Seeder
{
    public function run(): void
    {
        DemoGate::assertCanSeed();

        // Ensure platform wallet exists for purchase credits.
        app(WalletService::class)->getPlatformWallet();

        $timeline = DemoTimeline::fromNow();
        $ctx = new DemoContext(app(\App\Support\Demo\DemoBatchTracker::class), $timeline);
        $ctx->startBatch('Demo platform '.$timeline->monthsAgo(0)->toDateString(), 'DemoPlatformSeeder');

        $this->runChild(DemoAdminsSeeder::class, $ctx, $timeline);
        $this->runChild(DemoUsersSeeder::class, $ctx, $timeline);
        $this->runChild(DemoKycSeeder::class, $ctx, $timeline);
        $this->runChild(DemoWalletSeeder::class, $ctx, $timeline);
        $this->runChild(DemoPlatformOrdersSeeder::class, $ctx, $timeline);
        $this->runChild(DemoSupportSeeder::class, $ctx, $timeline);
        $this->runChild(DemoNotificationsSeeder::class, $ctx, $timeline);
        $this->runChild(DemoAuditSeeder::class, $ctx, $timeline);

        $this->rebuildWalletBalances();
        $this->runChild(DemoAnalyticsSeeder::class, $ctx, $timeline);

        $this->assertConsistency($ctx);

        foreach ($ctx->checklist as $line) {
            $this->command?->info($line);
        }
        $batchId = $ctx->tracker->batch()?->id;
        $this->command?->info('✓ Platform ready for demo'.($batchId ? " (batch #{$batchId})" : ''));
        $this->command?->info('  Launch cleanup: php artisan demo:clear --force');
    }

    private function runChild(string $class, DemoContext $ctx, DemoTimeline $timeline): void
    {
        /** @var Seeder $seeder */
        $seeder = app($class);
        $seeder->run($ctx, $timeline);
    }

    private function rebuildWalletBalances(): void
    {
        $wallets = Wallet::query()->whereNotNull('user_id')->get();
        foreach ($wallets as $wallet) {
            $wallet->forceFill([
                'balance' => $this->ledgerSum($wallet),
                'locked_balance' => $this->activeHolds($wallet),
            ])->save();
        }
    }

    private function ledgerSum(Wallet $wallet): float
    {
        return round((float) Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->sum('amount'), 2);
    }

    private function activeHolds(Wallet $wallet): float
    {
        return round((float) WalletHold::query()
            ->where('wallet_id', $wallet->id)
            ->where('status', WalletHoldStatus::Active->value)
            ->sum('amount'), 2);
    }

    private function assertConsistency(DemoContext $ctx): void
    {
        $alice = $ctx->member('alice');
        if ((int) $alice->kyc_level < 1) {
            throw new RuntimeException('Consistency: Alice should be KYC-verified.');
        }

        $approvedWithoutLevel = KycSubmission::query()
            ->where('status', 'approved')
            ->whereHas('user', fn ($q) => $q->where('kyc_level', '<', 1))
            ->count();
        if ($approvedWithoutLevel > 0) {
            throw new RuntimeException('Consistency: approved KYC without matching kyc_level.');
        }

        $resolvedWithoutReplies = SupportTicket::query()
            ->whereIn('status', ['resolved', 'closed'])
            ->whereDoesntHave('replies')
            ->count();
        if ($resolvedWithoutReplies > 0) {
            throw new RuntimeException('Consistency: resolved/closed tickets missing replies.');
        }

        $wallet = Wallet::query()->where('user_id', $alice->id)->first();
        if (! $wallet) {
            throw new RuntimeException('Consistency: Alice wallet missing.');
        }
        if (abs((float) $wallet->balance - $this->ledgerSum($wallet)) > 0.05) {
            throw new RuntimeException('Consistency: Alice wallet balance does not match ledger.');
        }

        foreach (Wallet::query()->where('type', 'user')->cursor() as $w) {
            $sum = $this->ledgerSum($w);
            if ($sum < -0.05) {
                throw new RuntimeException("Consistency: wallet #{$w->id} ledger is negative ({$sum}).");
            }
            if (abs((float) $w->balance - $sum) > 0.05) {
                throw new RuntimeException("Consistency: wallet #{$w->id} balance ≠ ledger.");
            }
            if (abs((float) $w->locked_balance - $this->activeHolds($w)) > 0.05) {
                throw new RuntimeException("Consistency: wallet #{$w->id} locked_balance ≠ active holds.");
            }
        }

        $validTypes = array_map(fn (TransactionType $t) => $t->value, TransactionType::cases());
        $invalidTypes = Transaction::query()->whereNotIn('type', $validTypes)->count();
        if ($invalidTypes > 0) {
            throw new RuntimeException('Consistency: invalid TransactionType values present.');
        }

        $hasCatalog = PlatformProduct::query()->published()->exists();
        if ($hasCatalog && Order::query()->where('source', 'platform')->count() < 45) {
            throw new RuntimeException('Consistency: expected ≥45 platform orders.');
        }
        if (SupportTicket::query()->count() < 35) {
            throw new RuntimeException('Consistency: expected ≥35 support tickets.');
        }
        if (KycSubmission::query()->count() < 18) {
            throw new RuntimeException('Consistency: expected ≥18 KYC submissions.');
        }
        $completedTransactions = Transaction::query()->where('status', 'completed')->count();
        if ($completedTransactions < 150) {
            throw new RuntimeException("Consistency: expected ≥150 completed transactions (found {$completedTransactions}).");
        }

        if (User::role('admin')->count() < 1) {
            throw new RuntimeException('Consistency: expected super admin persona.');
        }
        foreach (['demo_finance', 'demo_compliance', 'demo_support', 'demo_moderator'] as $role) {
            if (User::role($role)->count() < 1) {
                throw new RuntimeException("Consistency: expected {$role} persona.");
            }
        }

        if (UserActivity::query()->where('context_key', 'like', 'dashboard.%')->count() < 1) {
            throw new RuntimeException('Consistency: expected route-level dashboard activity.');
        }

        if (AnalyticsKpiSnapshot::query()->where('period', 'daily')->count() < 7) {
            throw new RuntimeException('Consistency: expected multi-day KPI snapshots for charts.');
        }

        if ($hasCatalog && ! Order::query()->where('user_id', $alice->id)->where('status', 'completed')->exists()) {
            throw new RuntimeException('Consistency: Alice should have a completed platform order.');
        }
    }
}
