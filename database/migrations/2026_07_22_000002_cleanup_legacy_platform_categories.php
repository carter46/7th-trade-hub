<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Run AFTER catalog:backfill-hierarchy and cutover code is deployed.
 * Drops legacy platform_categories + platform_category_id.
 * Keeps platform_products.product_type (string) nullable for dual-read / redirects.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_products') && Schema::hasColumn('platform_products', 'platform_category_id')) {
            Schema::table('platform_products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('platform_category_id');
            });
        }

        // Keep product_type string for dual-read / legacy redirects; make nullable via raw SQL when needed.
        if (Schema::hasTable('platform_products') && Schema::hasColumn('platform_products', 'product_type')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                Schema::getConnection()->statement(
                    'ALTER TABLE platform_products MODIFY product_type VARCHAR(40) NULL'
                );
            } elseif (in_array($driver, ['pgsql', 'postgres'], true)) {
                Schema::getConnection()->statement(
                    'ALTER TABLE platform_products ALTER COLUMN product_type DROP NOT NULL'
                );
            }
            // sqlite (tests): recreate not required — column already accepts null in practice for inserts with value
        }

        Schema::dropIfExists('platform_categories');
    }

    public function down(): void
    {
        if (! Schema::hasTable('platform_categories')) {
            Schema::create('platform_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('platform_categories')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('product_type', 40);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('banner_image')->nullable();
                $table->string('card_image')->nullable();
                $table->string('short_description', 500)->nullable();
                $table->string('hero_title')->nullable();
                $table->string('hero_subtitle', 500)->nullable();
                $table->json('benefits')->nullable();
                $table->json('faq')->nullable();
                $table->timestamps();
                $table->index('product_type');
            });
        }

        if (Schema::hasTable('platform_products') && ! Schema::hasColumn('platform_products', 'platform_category_id')) {
            Schema::table('platform_products', function (Blueprint $table) {
                $table->foreignId('platform_category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('platform_categories')
                    ->nullOnDelete();
            });
        }
    }
};
