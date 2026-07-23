<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('actor_id');
                $table->index('correlation_id');
                $table->index(['module', 'action']);
            });
        } catch (\Throwable) {
            // Indexes may already exist.
        }
    }

    public function down(): void
    {
        // Non-destructive down for Hostinger SQL deploys.
    }
};
