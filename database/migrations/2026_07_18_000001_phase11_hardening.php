<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
                }
                if (! Schema::hasColumn('categories', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('is_active');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'source')) {
                    $table->string('source', 20)->default('marketplace')->after('id');
                }
                if (! Schema::hasColumn('orders', 'total_amount')) {
                    $table->decimal('total_amount', 18, 2)->nullable()->after('amount');
                }
                if (! Schema::hasColumn('orders', 'idempotency_key')) {
                    $table->string('idempotency_key', 64)->nullable()->after('reference');
                }
            });

            $indexes = collect(DB::select('SHOW INDEX FROM `orders`'))->pluck('Key_name')->all();

            if (Schema::hasColumn('orders', 'idempotency_key') && ! in_array('orders_idempotency_key_unique', $indexes, true)) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->unique('idempotency_key');
                });
            }

            if (! in_array('orders_source_index', $indexes, true)) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->index('source');
                });
            }

            DB::statement('ALTER TABLE `orders` MODIFY `amount` DECIMAL(18,2) NOT NULL');
        }

        if (Schema::hasTable('platform_categories') && ! $this->indexExists('platform_categories', 'platform_categories_product_type_index')) {
            Schema::table('platform_categories', function (Blueprint $table) {
                $table->index('product_type');
            });
        }

        if (Schema::hasTable('favorites') && ! $this->indexExists('favorites', 'favorites_favoritable_index')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->index(['favoritable_type', 'favoritable_id'], 'favorites_favoritable_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'idempotency_key')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique(['idempotency_key']);
                $table->dropColumn('idempotency_key');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains(fn ($row) => ($row->Key_name ?? '') === $indexName);
    }
};
