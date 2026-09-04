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
            if (! Schema::hasColumn('user_tools', 'shutdown_resume_expires_at')) {
                $table->timestamp('shutdown_resume_expires_at')->nullable()->after('subscription_end_reason');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_tools')) {
            return;
        }

        Schema::table('user_tools', function (Blueprint $table) {
            if (Schema::hasColumn('user_tools', 'shutdown_resume_expires_at')) {
                $table->dropColumn('shutdown_resume_expires_at');
            }
        });
    }
};
