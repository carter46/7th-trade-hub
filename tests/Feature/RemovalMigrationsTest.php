<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/**
 * Runs the two destructive removal migrations against re-created legacy
 * tables. No wrapping transaction, to mirror MySQL where each statement
 * commits on its own, so every test starts from a fresh schema instead.
 */
class RemovalMigrationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
    }

    private const CRYPTO_MIGRATION = '2026_09_27_000001_remove_crypto_exchange.php';

    private const MARKETPLACE_MIGRATION = '2026_09_27_000002_remove_escrow_and_marketplace.php';

    private const CRYPTO_TABLES = [
        'wallet_balance_history', 'incoming_crypto_transactions', 'crypto_sell_requests',
        'crypto_deposit_wallets', 'exchange_rate_history', 'otc_pricing_settings', 'exchange_rates',
    ];

    private const MARKETPLACE_TABLES = [
        'reviews', 'watchlists', 'messages', 'escrows', 'listing_versions', 'listings',
        'marketplace_products', 'categories',
    ];

    public function test_crypto_migration_blocks_open_sell_requests_before_changing_anything(): void
    {
        $this->createCryptoTables();
        DB::table('crypto_sell_requests')->insert(['status' => 'waiting_deposit']);
        $ticket = SupportTicket::factory()->create(['category' => 'crypto_sell']);

        $this->assertMigrationBlocked(self::CRYPTO_MIGRATION, 'still open');

        $this->assertSame('crypto_sell', $ticket->fresh()->category);
        foreach (self::CRYPTO_TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' must survive a blocked run');
        }
    }

    public function test_crypto_migration_blocks_unmatched_incoming_deposits(): void
    {
        $this->createCryptoTables();
        DB::table('incoming_crypto_transactions')->insert(['tx_hash' => '0xabc', 'status' => 'detected']);

        $this->assertMigrationBlocked(self::CRYPTO_MIGRATION, 'incoming_crypto_transactions');
    }

    public function test_crypto_migration_blocks_expired_requests_with_a_submitted_tx_hash(): void
    {
        $this->createCryptoTables();
        DB::table('crypto_sell_requests')->insert(['status' => 'expired', 'tx_hash' => '0xdef']);

        $this->assertMigrationBlocked(self::CRYPTO_MIGRATION, 'tx_hash');
    }

    public function test_crypto_migration_only_drops_its_tables_and_leaves_user_data_untouched(): void
    {
        $this->createCryptoTables();
        $user = User::factory()->create();
        DB::table('crypto_sell_requests')->insert([
            ['status' => 'expired', 'tx_hash' => null, 'wallet_funding_id' => null],
            ['status' => 'rejected', 'tx_hash' => '0x1', 'wallet_funding_id' => null],
            ['status' => 'approved', 'tx_hash' => '0x2', 'wallet_funding_id' => 7],
        ]);
        DB::table('incoming_crypto_transactions')->insert([
            ['tx_hash' => '0x1', 'status' => 'ignored', 'matched_order_id' => null],
            ['tx_hash' => '0x2', 'status' => 'matched', 'matched_order_id' => 3],
        ]);
        SupportTicket::factory()->create(['user_id' => $user->id, 'category' => 'crypto_sell']);
        $this->insertUserNotification($user, 'crypto.sell_approved');
        $this->insertUserNotification($user, 'order.completed');
        Wallet::factory()->create(['user_id' => $user->id, 'balance' => 5000]);
        $before = $this->snapshotUserData();

        $this->runMigration(self::CRYPTO_MIGRATION);

        foreach (self::CRYPTO_TABLES as $table) {
            $this->assertFalse(Schema::hasTable($table), $table.' should be dropped');
        }
        $this->assertEquals($before, $this->snapshotUserData());

        $this->runMigration(self::CRYPTO_MIGRATION);
        $this->assertEquals($before, $this->snapshotUserData());
    }

    public function test_marketplace_migration_blocks_unsettled_escrows(): void
    {
        $this->createMarketplaceTables();
        DB::table('escrows')->insert(['status' => 'locked']);

        $this->assertMigrationBlocked(self::MARKETPLACE_MIGRATION, 'escrows not released or refunded');
        $this->assertTrue(Schema::hasTable('escrows'));
        $this->assertTrue(Schema::hasColumn('orders', 'listing_id'));
    }

    public function test_marketplace_migration_blocks_active_escrow_or_listing_holds(): void
    {
        $this->createMarketplaceTables();
        $wallet = Wallet::factory()->create(['balance' => 10000, 'locked_balance' => 1000]);
        DB::table('wallet_holds')->insert([
            'wallet_id' => $wallet->id, 'reason_type' => 'listing', 'amount' => 1000, 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->assertMigrationBlocked(self::MARKETPLACE_MIGRATION, 'active escrow/listing wallet_holds');
    }

    public function test_marketplace_migration_blocks_open_marketplace_orders(): void
    {
        $this->createMarketplaceTables();
        Order::factory()->create(['source' => 'marketplace', 'status' => 'paid']);

        $this->assertMigrationBlocked(self::MARKETPLACE_MIGRATION, 'open marketplace orders');
    }

    public function test_marketplace_migration_drops_tables_and_leaves_user_data_untouched(): void
    {
        $this->createMarketplaceTables();
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 10000, 'locked_balance' => 1000]);
        DB::table('wallet_holds')->insert([
            ['wallet_id' => $wallet->id, 'reason_type' => 'withdrawal', 'amount' => 1000, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['wallet_id' => $wallet->id, 'reason_type' => 'listing', 'amount' => 500, 'status' => 'released', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('escrows')->insert(['status' => 'released']);
        DB::table('listings')->insert(['id' => 9]);
        $history = Order::factory()->create(['user_id' => $user->id, 'source' => 'marketplace', 'status' => 'completed']);
        DB::table('orders')->where('id', $history->id)->update(['listing_id' => 9]);
        DB::table('order_items')->insert([
            'order_id' => $history->id, 'item_type' => 'listing', 'item_id' => 9,
            'unit_price' => 100, 'line_total' => 100, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $transaction = Transaction::factory()->create(['user_id' => $user->id, 'wallet_id' => $wallet->id]);
        DB::table('transactions')->where('id', $transaction->id)->update(['escrow_id' => 1]);
        DB::table('favorites')->insert([
            'user_id' => $user->id, 'favoritable_type' => 'App\\Models\\Listing', 'favoritable_id' => 9,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        SupportTicket::factory()->create(['user_id' => $user->id, 'category' => 'marketplace']);
        $this->insertUserNotification($user, 'escrow.released', '/dashboard/sales');
        $this->insertUserNotification($user, 'order.completed', '/dashboard/service-orders');
        DB::table('system_settings')->updateOrInsert(['key' => 'site_tagline'], ['value' => 'Connecting markets, empowering traders.']);
        DB::table('service_categories')->insert([
            'name' => 'Trust & Escrow', 'slug' => 'trust-escrow', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $before = $this->snapshotUserData();

        $this->runMigration(self::MARKETPLACE_MIGRATION);

        foreach (self::MARKETPLACE_TABLES as $table) {
            $this->assertFalse(Schema::hasTable($table), $table.' should be dropped');
        }
        $this->assertEquals($before, $this->snapshotUserData());
        $this->assertSame(9, (int) DB::table('orders')->where('id', $history->id)->value('listing_id'));
        $this->assertSame(1, (int) DB::table('transactions')->where('id', $transaction->id)->value('escrow_id'));
        $this->assertSame([], array_filter(
            Schema::getForeignKeys('orders'),
            fn ($fk) => $fk['columns'] === ['listing_id']
        ));

        $this->assertSame('platform', (new Order)->source);

        $this->runMigration(self::MARKETPLACE_MIGRATION);
        $this->assertEquals($before, $this->snapshotUserData());
    }

    public function test_both_removal_migrations_refuse_to_roll_back(): void
    {
        foreach ([self::CRYPTO_MIGRATION, self::MARKETPLACE_MIGRATION] as $file) {
            try {
                $this->migration($file)->down();
                $this->fail($file.' down() should throw');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('irreversible', $e->getMessage());
            }
        }
    }

    private function assertMigrationBlocked(string $file, string $expectedMessage): void
    {
        try {
            $this->runMigration($file);
            $this->fail($file.' should have been blocked by its guards');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString($expectedMessage, $e->getMessage());
        }
    }

    private function runMigration(string $file): void
    {
        $this->migration($file)->up();
    }

    private function migration(string $file): object
    {
        return require database_path('migrations/'.$file);
    }

    /** @return array<string, list<array<string, mixed>>> */
    private function snapshotUserData(): array
    {
        $snapshot = [];
        foreach ([
            'users', 'wallets', 'wallet_holds', 'wallet_fundings', 'transactions', 'orders', 'order_items',
            'user_notifications', 'admin_notifications', 'favorites', 'support_tickets', 'withdrawals',
            'system_settings', 'service_categories', 'product_types', 'platform_products', 'integration_providers',
        ] as $table) {
            if (Schema::hasTable($table)) {
                $snapshot[$table] = DB::table($table)->orderBy('id')->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();
            }
        }

        return $snapshot;
    }

    private function insertUserNotification(User $user, string $type, ?string $url = null): int
    {
        return DB::table('user_notifications')->insertGetId([
            'user_id' => $user->id, 'type' => $type, 'title' => $type, 'action_url' => $url,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function createCryptoTables(): void
    {
        Schema::create('crypto_sell_requests', function (Blueprint $table) {
            $table->id();
            $table->string('status', 40);
            $table->string('tx_hash')->nullable();
            $table->unsignedBigInteger('wallet_funding_id')->nullable();
        });
        Schema::create('incoming_crypto_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tx_hash');
            $table->string('status', 40)->default('detected');
            $table->foreignId('matched_order_id')->nullable()->constrained('crypto_sell_requests')->nullOnDelete();
        });
        foreach (['wallet_balance_history', 'crypto_deposit_wallets', 'exchange_rate_history', 'otc_pricing_settings', 'exchange_rates'] as $table) {
            Schema::create($table, fn (Blueprint $t) => $t->id());
        }
    }

    private function createMarketplaceTables(): void
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('locked');
        });
        foreach (['reviews', 'watchlists', 'messages', 'listing_versions', 'listings', 'marketplace_products', 'categories'] as $table) {
            Schema::create($table, fn (Blueprint $t) => $t->id());
        }
        $this->restoreLegacyForeignKey('orders', 'listing_id', 'listings');
        $this->restoreLegacyForeignKey('transactions', 'escrow_id', 'escrows');
    }

    private function restoreLegacyForeignKey(string $table, string $column, string $references): void
    {
        Schema::table($table, function (Blueprint $t) use ($table, $column, $references) {
            if (Schema::hasColumn($table, $column)) {
                $t->foreign($column)->references('id')->on($references)->nullOnDelete();
            } else {
                $t->foreignId($column)->nullable()->constrained($references)->nullOnDelete();
            }
        });
    }
}
