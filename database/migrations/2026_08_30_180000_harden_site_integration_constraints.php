<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_tools')) {
            Schema::table('user_tools', function (Blueprint $table) {
                if (! $this->indexExists('user_tools', 'user_tools_order_item_id_unique')) {
                    $table->unique('order_item_id');
                }
            });

            // Prefer restrict over cascade so catalog admin cannot wipe customer tools.
            if (Schema::hasColumn('user_tools', 'platform_product_id')) {
                Schema::table('user_tools', function (Blueprint $table) {
                    $table->dropForeign(['platform_product_id']);
                });
                Schema::table('user_tools', function (Blueprint $table) {
                    $table->foreign('platform_product_id')
                        ->references('id')
                        ->on('platform_products')
                        ->restrictOnDelete();
                });
            }
        }

        if (Schema::hasTable('site_integrations')) {
            Schema::table('site_integrations', function (Blueprint $table) {
                if (! $this->indexExists('site_integrations', 'site_integrations_client_id_unique')) {
                    $table->unique('client_id');
                }
            });
        }

        if (Schema::hasTable('user_tool_integrations')) {
            Schema::table('user_tool_integrations', function (Blueprint $table) {
                if (! $this->indexExists('user_tool_integrations', 'user_tool_integrations_client_id_unique')) {
                    $table->unique('client_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_tools')) {
            Schema::table('user_tools', function (Blueprint $table) {
                $table->dropUnique(['order_item_id']);
            });

            Schema::table('user_tools', function (Blueprint $table) {
                $table->dropForeign(['platform_product_id']);
            });
            Schema::table('user_tools', function (Blueprint $table) {
                $table->foreign('platform_product_id')
                    ->references('id')
                    ->on('platform_products')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('site_integrations')) {
            Schema::table('site_integrations', function (Blueprint $table) {
                $table->dropUnique(['client_id']);
            });
        }

        if (Schema::hasTable('user_tool_integrations')) {
            Schema::table('user_tool_integrations', function (Blueprint $table) {
                $table->dropUnique(['client_id']);
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");
            foreach ($indexes as $index) {
                if (($index->name ?? '') === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = Schema::getConnection()->getDatabaseName();
        $rows = DB::select(
            'select 1 from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
            [$database, $table, $indexName]
        );

        return $rows !== [];
    }
};
