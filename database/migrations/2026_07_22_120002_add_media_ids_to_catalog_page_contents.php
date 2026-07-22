<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('catalog_page_contents')) {
            return;
        }

        Schema::table('catalog_page_contents', function (Blueprint $table) {
            if (! Schema::hasColumn('catalog_page_contents', 'banner_media_id')) {
                $table->foreignId('banner_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            }
            if (! Schema::hasColumn('catalog_page_contents', 'card_media_id')) {
                $table->foreignId('card_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('catalog_page_contents')) {
            return;
        }
        Schema::table('catalog_page_contents', function (Blueprint $table) {
            if (Schema::hasColumn('catalog_page_contents', 'banner_media_id')) {
                $table->dropConstrainedForeignId('banner_media_id');
            }
            if (Schema::hasColumn('catalog_page_contents', 'card_media_id')) {
                $table->dropConstrainedForeignId('card_media_id');
            }
        });
    }
};
