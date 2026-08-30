<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_product_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_product_variants', 'description')) {
                $table->text('description')->nullable()->after('label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('platform_product_variants', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
