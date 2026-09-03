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
            if (! Schema::hasColumn('user_tools', 'subscription_end_reason')) {
                $table->string('subscription_end_reason', 32)->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_tools')) {
            return;
        }

        Schema::table('user_tools', function (Blueprint $table) {
            if (Schema::hasColumn('user_tools', 'subscription_end_reason')) {
                $table->dropColumn('subscription_end_reason');
            }
        });
    }
};
