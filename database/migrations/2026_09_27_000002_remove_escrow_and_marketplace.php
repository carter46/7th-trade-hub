<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permanently removes escrow and the peer marketplace (listings, marketplace
 * categories/products, messages, reviews, watchlists) by dropping their tables only.
 *
 * No other row is changed or deleted: users, wallets, wallet holds, transactions,
 * orders and order items, fundings, notifications, favorites, support tickets,
 * catalog and site settings all stay exactly as they are. `orders.listing_id` and
 * `transactions.escrow_id` keep their values; only their foreign keys to the
 * dropped tables are removed, because MySQL cannot drop a table that is still referenced.
 */
return new class extends Migration
{
    /** Hold reasons that belonged to the removed features. */
    private const REMOVED_HOLD_REASONS = ['escrow', 'listing', 'listing_hold'];

    /** Escrow statuses where no money is still held. */
    private const CLOSED_ESCROW_STATUSES = ['released', 'refunded', 'partial_refund'];

    /** Marketplace order statuses that can still move money. */
    private const OPEN_ORDER_STATUSES = ['pending', 'processing', 'paid'];

    public function up(): void
    {
        $this->guardUnresolvedRecords();

        $this->dropForeignKeys('orders', 'listing_id');
        $this->dropForeignKeys('transactions', 'escrow_id');

        foreach ([
            'reviews',
            'watchlists',
            'messages',
            'escrows',
            'listing_versions',
            'listings',
            'marketplace_products',
            'categories',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        throw new RuntimeException('The escrow and marketplace removal is irreversible. Restore the pre-deploy database backup instead.');
    }

    /** Read-only: stops before any change while money could still be tied to the removed features. */
    private function guardUnresolvedRecords(): void
    {
        $problems = [];

        if (Schema::hasTable('escrows')) {
            $ids = DB::table('escrows')->whereNotIn('status', self::CLOSED_ESCROW_STATUSES)->pluck('id');
            if ($ids->isNotEmpty()) {
                $problems[] = 'escrows not released or refunded: '.$ids->implode(', ');
            }
        }

        if (Schema::hasTable('wallet_holds')) {
            $ids = DB::table('wallet_holds')
                ->whereIn('reason_type', self::REMOVED_HOLD_REASONS)
                ->where('status', 'active')
                ->pluck('id');
            if ($ids->isNotEmpty()) {
                $problems[] = 'active escrow/listing wallet_holds: '.$ids->implode(', ');
            }
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'source')) {
            $ids = DB::table('orders')
                ->where('source', 'marketplace')
                ->whereIn('status', self::OPEN_ORDER_STATUSES)
                ->pluck('id');
            if ($ids->isNotEmpty()) {
                $problems[] = 'open marketplace orders (complete or cancel them): '.$ids->implode(', ');
            }
        }

        if ($problems !== []) {
            throw new RuntimeException(
                'Cannot remove escrow and the marketplace until these are resolved manually: '
                .implode('; ', $problems).'.'
            );
        }
    }

    /** Removes only the constraint so the referenced table can be dropped; column values are kept. */
    private function dropForeignKeys(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if ($foreignKey['columns'] === [$column]) {
                // SQLite cannot drop a foreign key by name, only by its columns.
                Schema::table($table, fn (Blueprint $t) => $t->dropForeign($isSqlite ? [$column] : $foreignKey['name']));
            }
        }
    }
};
