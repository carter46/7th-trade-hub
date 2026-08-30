<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method', 20)->nullable()->after('status');
            }
            if (! Schema::hasColumn('orders', 'payment_provider')) {
                $table->string('payment_provider', 40)->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('orders', 'provider_payment_reference')) {
                $table->string('provider_payment_reference', 64)->nullable()->after('payment_provider');
            }
            if (! Schema::hasColumn('orders', 'provider_transaction_reference')) {
                $table->string('provider_transaction_reference', 128)->nullable()->after('provider_payment_reference');
            }
            if (! Schema::hasColumn('orders', 'checkout_url')) {
                $table->text('checkout_url')->nullable()->after('provider_transaction_reference');
            }
            if (! Schema::hasColumn('orders', 'checkout_expires_at')) {
                $table->timestamp('checkout_expires_at')->nullable()->after('checkout_url');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! $this->indexExists('orders', 'orders_provider_payment_reference_index')) {
                $table->index('provider_payment_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'checkout_expires_at',
                'checkout_url',
                'provider_transaction_reference',
                'provider_payment_reference',
                'payment_provider',
                'payment_method',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $rows = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($rows as $row) {
                $name = is_object($row) ? ($row->name ?? null) : ($row['name'] ?? null);
                if ($name === $index) {
                    return true;
                }
            }

            return false;
        }

        $database = $connection->getDatabaseName();
        $result = $connection->selectOne(
            'select count(*) as aggregate from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ?',
            [$database, $table, $index]
        );

        return (int) (is_object($result) ? $result->aggregate : ($result['aggregate'] ?? 0)) > 0;
    }
};
