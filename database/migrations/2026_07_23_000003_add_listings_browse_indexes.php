<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            if (! $this->indexExists('listings', 'listings_browse_product_index')) {
                $table->index(
                    ['status', 'is_active', 'marketplace_product_id'],
                    'listings_browse_product_index'
                );
            }
            if (! $this->indexExists('listings', 'listings_browse_created_index')) {
                $table->index(
                    ['status', 'is_active', 'created_at'],
                    'listings_browse_created_index'
                );
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            if ($this->indexExists('listings', 'listings_browse_product_index')) {
                $table->dropIndex('listings_browse_product_index');
            }
            if ($this->indexExists('listings', 'listings_browse_created_index')) {
                $table->dropIndex('listings_browse_created_index');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $sm = Schema::getConnection()->getSchemaBuilder();
        $indexes = $sm->getIndexes($table);

        foreach ($indexes as $info) {
            if (($info['name'] ?? '') === $index) {
                return true;
            }
        }

        return false;
    }
};
