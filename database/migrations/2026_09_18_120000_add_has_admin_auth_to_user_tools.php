<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_tools')) {
            return;
        }

        Schema::table('user_tools', function (Blueprint $table) {
            if (! Schema::hasColumn('user_tools', 'has_admin_auth')) {
                $table->boolean('has_admin_auth')->default(true)->after('admin_password');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_tools')) {
            return;
        }

        Schema::table('user_tools', function (Blueprint $table) {
            if (Schema::hasColumn('user_tools', 'has_admin_auth')) {
                $table->dropColumn('has_admin_auth');
            }
        });
    }
};
