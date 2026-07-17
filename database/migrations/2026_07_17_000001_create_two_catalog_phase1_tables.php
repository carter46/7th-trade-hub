<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('categories', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('is_active');
            }
        });

        if (! Schema::hasTable('platform_categories')) {
            Schema::create('platform_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('platform_categories')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('product_type', 40);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index('product_type');
            });
        }

        if (! Schema::hasTable('platform_products')) {
            Schema::create('platform_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('platform_category_id')->nullable()->constrained('platform_categories')->nullOnDelete();
                $table->string('product_type', 40);
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('short_description', 500)->nullable();
                $table->text('description')->nullable();
                $table->string('status', 20)->default('draft');
                $table->boolean('is_featured')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('hero_image')->nullable();
                $table->string('demo_url')->nullable();
                $table->string('demo_username')->nullable();
                $table->string('demo_password')->nullable();
                $table->string('industry')->nullable();
                $table->string('framework')->nullable();
                $table->boolean('is_responsive')->default(true);
                $table->boolean('is_seo_ready')->default(false);
                $table->string('support_period')->nullable();
                $table->json('features')->nullable();
                $table->json('requirements')->nullable();
                $table->json('whats_included')->nullable();
                $table->json('faqs')->nullable();
                $table->text('support_text')->nullable();
                $table->decimal('base_price', 18, 2)->default(0);
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['product_type', 'status']);
                $table->index(['is_featured', 'status']);
            });
        }

        if (! Schema::hasTable('platform_product_images')) {
            Schema::create('platform_product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('platform_product_id')->constrained('platform_products')->cascadeOnDelete();
                $table->string('path');
                $table->string('alt')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('platform_product_variants')) {
            Schema::create('platform_product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('platform_product_id')->constrained('platform_products')->cascadeOnDelete();
                $table->string('name');
                $table->string('label')->nullable();
                $table->string('sku')->nullable()->unique();
                $table->unsignedInteger('duration_months')->nullable();
                $table->decimal('price', 18, 2);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('exchange_rates')) {
            Schema::create('exchange_rates', function (Blueprint $table) {
                $table->id();
                $table->string('asset', 20)->unique();
                $table->decimal('buy_rate_ngn', 18, 2);
                $table->decimal('sell_rate_ngn', 18, 2);
                $table->decimal('minimum_amount', 18, 8)->nullable();
                $table->decimal('maximum_amount', 18, 8)->nullable();
                $table->string('processing_time')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'source')) {
                $table->string('source', 20)->default('marketplace')->after('id');
            }
            if (! Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 18, 2)->nullable()->after('amount');
            }
        });

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('item_type', 40);
                $table->unsignedBigInteger('item_id');
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 18, 2);
                $table->decimal('line_total', 18, 2);
                $table->foreignId('platform_product_variant_id')->nullable()->constrained('platform_product_variants')->nullOnDelete();
                $table->json('options')->nullable();
                $table->timestamps();

                $table->index(['item_type', 'item_id']);
            });
        }

        if (! Schema::hasTable('favorites')) {
            Schema::create('favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('favoritable_type');
                $table->unsignedBigInteger('favoritable_id');
                $table->timestamps();

                $table->unique(['user_id', 'favoritable_type', 'favoritable_id'], 'favorites_user_favoritable_unique');
                $table->index(['favoritable_type', 'favoritable_id'], 'favorites_favoritable_index');
            });
        }

        if (! Schema::hasTable('product_reviews')) {
            Schema::create('product_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('platform_product_id')->constrained('platform_products')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'platform_product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('order_items');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('orders', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
        });

        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('platform_product_variants');
        Schema::dropIfExists('platform_product_images');
        Schema::dropIfExists('platform_products');
        Schema::dropIfExists('platform_categories');

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
            if (Schema::hasColumn('categories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
