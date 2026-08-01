<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('social_links')) {
            return;
        }

        Schema::table('social_links', function (Blueprint $table) {
            if (! Schema::hasColumn('social_links', 'icon_media_id')) {
                $table->unsignedBigInteger('icon_media_id')->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('social_links') || ! Schema::hasColumn('social_links', 'icon_media_id')) {
            return;
        }

        Schema::table('social_links', function (Blueprint $table) {
            $table->dropColumn('icon_media_id');
        });
    }
};
