<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_categories')) {
            Schema::table('platform_categories', function (Blueprint $table) {
                if (! Schema::hasColumn('platform_categories', 'banner_image')) {
                    $table->string('banner_image')->nullable()->after('is_active');
                }
                if (! Schema::hasColumn('platform_categories', 'card_image')) {
                    $table->string('card_image')->nullable()->after('banner_image');
                }
                if (! Schema::hasColumn('platform_categories', 'short_description')) {
                    $table->string('short_description', 500)->nullable()->after('card_image');
                }
                if (! Schema::hasColumn('platform_categories', 'hero_title')) {
                    $table->string('hero_title')->nullable()->after('short_description');
                }
                if (! Schema::hasColumn('platform_categories', 'hero_subtitle')) {
                    $table->string('hero_subtitle', 500)->nullable()->after('hero_title');
                }
                if (! Schema::hasColumn('platform_categories', 'benefits')) {
                    $table->json('benefits')->nullable()->after('hero_subtitle');
                }
                if (! Schema::hasColumn('platform_categories', 'faq')) {
                    $table->json('faq')->nullable()->after('benefits');
                }
            });
        }

        if (! Schema::hasTable('catalog_page_contents')) {
            Schema::create('catalog_page_contents', function (Blueprint $table) {
                $table->id();
                $table->string('scope', 20);
                $table->string('key', 80);
                $table->string('banner_image')->nullable();
                $table->string('card_image')->nullable();
                $table->string('short_description', 500)->nullable();
                $table->string('hero_title')->nullable();
                $table->string('hero_subtitle', 500)->nullable();
                $table->json('benefits')->nullable();
                $table->json('faq')->nullable();
                $table->timestamps();

                $table->unique(['scope', 'key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_page_contents');

        if (Schema::hasTable('platform_categories')) {
            Schema::table('platform_categories', function (Blueprint $table) {
                foreach (['banner_image', 'card_image', 'short_description', 'hero_title', 'hero_subtitle', 'benefits', 'faq'] as $column) {
                    if (Schema::hasColumn('platform_categories', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
