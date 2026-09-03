<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('platform_products')) {
            Schema::table('platform_products', function (Blueprint $table) {
                if (! Schema::hasColumn('platform_products', 'tutorial_url')) {
                    $table->string('tutorial_url')->nullable()->after('demo_password');
                }
                if (! Schema::hasColumn('platform_products', 'tutorial_description')) {
                    $table->text('tutorial_description')->nullable()->after('tutorial_url');
                }
            });
        }

        if (Schema::hasTable('user_tools')) {
            Schema::table('user_tools', function (Blueprint $table) {
                foreach (['tutorial_description', 'tutorial_url'] as $column) {
                    if (Schema::hasColumn('user_tools', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('platform_products')) {
            Schema::table('platform_products', function (Blueprint $table) {
                foreach (['tutorial_description', 'tutorial_url'] as $column) {
                    if (Schema::hasColumn('platform_products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('user_tools')) {
            Schema::table('user_tools', function (Blueprint $table) {
                if (! Schema::hasColumn('user_tools', 'tutorial_url')) {
                    $table->string('tutorial_url')->nullable()->after('livechat_password');
                }
                if (! Schema::hasColumn('user_tools', 'tutorial_description')) {
                    $table->text('tutorial_description')->nullable()->after('tutorial_url');
                }
            });
        }
    }
};
