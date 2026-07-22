<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_categories')) {
            Schema::table('service_categories', function (Blueprint $table) {
                if (! Schema::hasColumn('service_categories', 'banner_media_id')) {
                    $table->foreignId('banner_media_id')->nullable()->after('banner_image')->constrained('media_assets')->nullOnDelete();
                }
                if (! Schema::hasColumn('service_categories', 'card_media_id')) {
                    $table->foreignId('card_media_id')->nullable()->after('card_image')->constrained('media_assets')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('product_types')) {
            Schema::table('product_types', function (Blueprint $table) {
                if (! Schema::hasColumn('product_types', 'banner_media_id')) {
                    $table->foreignId('banner_media_id')->nullable()->after('banner_image')->constrained('media_assets')->nullOnDelete();
                }
                if (! Schema::hasColumn('product_types', 'card_media_id')) {
                    $table->foreignId('card_media_id')->nullable()->after('card_image')->constrained('media_assets')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('platform_products')) {
            Schema::table('platform_products', function (Blueprint $table) {
                if (! Schema::hasColumn('platform_products', 'hero_media_id')) {
                    $table->foreignId('hero_media_id')->nullable()->after('hero_image')->constrained('media_assets')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('platform_products') && Schema::hasColumn('platform_products', 'hero_media_id')) {
            Schema::table('platform_products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('hero_media_id');
            });
        }
        if (Schema::hasTable('product_types')) {
            Schema::table('product_types', function (Blueprint $table) {
                if (Schema::hasColumn('product_types', 'banner_media_id')) {
                    $table->dropConstrainedForeignId('banner_media_id');
                }
                if (Schema::hasColumn('product_types', 'card_media_id')) {
                    $table->dropConstrainedForeignId('card_media_id');
                }
            });
        }
        if (Schema::hasTable('service_categories')) {
            Schema::table('service_categories', function (Blueprint $table) {
                if (Schema::hasColumn('service_categories', 'banner_media_id')) {
                    $table->dropConstrainedForeignId('banner_media_id');
                }
                if (Schema::hasColumn('service_categories', 'card_media_id')) {
                    $table->dropConstrainedForeignId('card_media_id');
                }
            });
        }
    }
};
