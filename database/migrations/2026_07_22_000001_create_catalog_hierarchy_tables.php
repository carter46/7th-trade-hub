<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_categories')) {
            Schema::create('service_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('banner_image')->nullable();
                $table->string('card_image')->nullable();
                $table->string('short_description', 500)->nullable();
                $table->string('hero_title')->nullable();
                $table->string('hero_subtitle', 500)->nullable();
                $table->json('benefits')->nullable();
                $table->json('faq')->nullable();
                $table->string('mode', 40)->default('catalog'); // catalog | marketplace_link
                $table->string('cta_label')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_types')) {
            Schema::create('product_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
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

                $table->index(['service_category_id', 'is_active']);
            });
        }

        if (Schema::hasTable('platform_products')) {
            Schema::table('platform_products', function (Blueprint $table) {
                if (! Schema::hasColumn('platform_products', 'product_type_id')) {
                    $table->foreignId('product_type_id')
                        ->nullable()
                        ->after('platform_category_id')
                        ->constrained('product_types')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('platform_products', 'provider')) {
                    $table->string('provider', 80)->nullable()->after('meta');
                }
                if (! Schema::hasColumn('platform_products', 'provider_product_id')) {
                    $table->string('provider_product_id')->nullable()->after('provider');
                }
                if (! Schema::hasColumn('platform_products', 'provider_sku')) {
                    $table->string('provider_sku')->nullable()->after('provider_product_id');
                }
                if (! Schema::hasColumn('platform_products', 'provider_meta')) {
                    $table->json('provider_meta')->nullable()->after('provider_sku');
                }
                if (! Schema::hasColumn('platform_products', 'fulfillment_mode')) {
                    $table->string('fulfillment_mode', 40)->default('manual')->after('provider_meta');
                }
                if (! Schema::hasColumn('platform_products', 'auto_renew')) {
                    $table->boolean('auto_renew')->default(false)->after('fulfillment_mode');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('platform_products')) {
            Schema::table('platform_products', function (Blueprint $table) {
                if (Schema::hasColumn('platform_products', 'product_type_id')) {
                    $table->dropConstrainedForeignId('product_type_id');
                }
                foreach (['provider', 'provider_product_id', 'provider_sku', 'provider_meta', 'fulfillment_mode', 'auto_renew'] as $column) {
                    if (Schema::hasColumn('platform_products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('product_types');
        Schema::dropIfExists('service_categories');
    }
};
