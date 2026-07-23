<?php

namespace Tests\Feature\Demo;

use App\Models\AnalyticsKpiSnapshot;
use App\Models\AuditLog;
use App\Models\Escrow;
use App\Models\KycSubmission;
use App\Models\Order;
use App\Models\SupportTicketReply;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Wallet;
use Database\Seeders\Demo\DemoPlatformSeeder;
use Database\Seeders\MarketplaceListingSeeder;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DemoPlatformSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_platform_seeder_builds_consistent_graph(): void
    {
        config(['demo.allow_demo_data' => true]);

        $this->seed([
            SystemSettingSeeder::class,
            MarketplaceListingSeeder::class,
            DemoPlatformSeeder::class,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'super.admin@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'finance.admin@example.com']);

        $this->assertGreaterThanOrEqual(1, User::role('admin')->count());
        $this->assertGreaterThanOrEqual(1, User::role('demo_finance')->count());
        $this->assertGreaterThanOrEqual(1, User::role('demo_compliance')->count());
        $this->assertGreaterThanOrEqual(1, User::role('demo_support')->count());
        $this->assertGreaterThanOrEqual(1, User::role('demo_moderator')->count());

        $this->assertGreaterThanOrEqual(1, KycSubmission::query()->where('status', 'approved')->count());
        $this->assertGreaterThanOrEqual(1, SupportTicketReply::query()->count());

        $this->assertGreaterThanOrEqual(1, Escrow::query()->where('status', 'locked')->count());
        $this->assertGreaterThanOrEqual(1, Escrow::query()->where('status', 'released')->count());
        $this->assertGreaterThanOrEqual(1, Escrow::query()->where('status', 'refunded')->count());
        $this->assertGreaterThanOrEqual(1, Escrow::query()->where('status', 'disputed')->count());

        $this->assertGreaterThanOrEqual(1, Transaction::query()
            ->where('type', 'platform_fee')
            ->where('amount', '>', 0)
            ->count());

        $this->assertGreaterThanOrEqual(1, UserActivity::query()->where('context_key', 'dashboard.marketplace.view')->count());
        $this->assertGreaterThanOrEqual(1, UserActivity::query()->where('context_key', 'dashboard.wallet.view')->count());
        $this->assertGreaterThanOrEqual(1, AuditLog::query()->count());
        $this->assertGreaterThanOrEqual(1, AnalyticsKpiSnapshot::query()->where('period', 'daily')->count());

        $alice = User::query()->where('email', 'alice@example.com')->firstOrFail();
        $this->assertGreaterThanOrEqual(1, (int) $alice->kyc_level);
        $this->assertTrue(
            Order::query()->where('user_id', $alice->id)->where('status', 'completed')->exists()
        );

        $wallet = Wallet::query()->where('user_id', $alice->id)->firstOrFail();
        $ledger = (float) Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->where('status', 'completed')
            ->sum('amount');
        $this->assertEqualsWithDelta(round($ledger, 2), (float) $wallet->balance, 0.05);

        $registrationDays = User::query()
            ->selectRaw('date(created_at) as d')
            ->groupBy('d')
            ->pluck('d');
        $this->assertGreaterThan(1, $registrationDays->count(), 'Registrations should span multiple days');
    }

    public function test_demo_platform_seeder_refuses_without_allow_flag(): void
    {
        config(['demo.allow_demo_data' => false]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ALLOW_DEMO_DATA');

        app(DemoPlatformSeeder::class)->run();
    }

    public function test_demo_fresh_command_refuses_destructive_in_production_without_flag(): void
    {
        $this->app['env'] = 'production';
        config([
            'demo.allow_demo_data' => true,
            'demo.allow_destructive_seeders' => false,
        ]);

        $this->artisan('demo:fresh', ['--force' => true])
            ->expectsOutputToContain('Destructive demo:fresh refused')
            ->assertFailed();
    }

    public function test_demo_clear_removes_tagged_batch(): void
    {
        config(['demo.allow_demo_data' => true]);

        $this->seed([
            SystemSettingSeeder::class,
            MarketplaceListingSeeder::class,
            DemoPlatformSeeder::class,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
        $this->assertGreaterThan(0, \App\Models\DemoBatch::query()->active()->count());

        $this->artisan('demo:clear', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseMissing('users', ['email' => 'alice@example.com']);
        $this->assertSame(0, \App\Models\DemoBatch::query()->active()->count());
        $this->assertSame(0, \App\Models\KycSubmission::query()->count());
        $this->assertSame(0, \App\Models\SupportTicket::query()->count());
    }
}