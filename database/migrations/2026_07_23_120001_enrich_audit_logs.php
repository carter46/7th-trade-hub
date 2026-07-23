<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'actor_id')) {
                $table->unsignedBigInteger('actor_id')->nullable()->after('admin_id');
            }
            if (! Schema::hasColumn('audit_logs', 'actor_type')) {
                $table->string('actor_type', 30)->nullable()->after('actor_id');
            }
            if (! Schema::hasColumn('audit_logs', 'module')) {
                $table->string('module', 60)->nullable()->after('action');
            }
            if (! Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip');
            }
            if (! Schema::hasColumn('audit_logs', 'device')) {
                $table->string('device', 30)->nullable()->after('user_agent');
            }
            if (! Schema::hasColumn('audit_logs', 'browser')) {
                $table->string('browser', 60)->nullable()->after('device');
            }
            if (! Schema::hasColumn('audit_logs', 'country')) {
                $table->string('country', 2)->nullable()->after('browser');
            }
            if (! Schema::hasColumn('audit_logs', 'reason')) {
                $table->text('reason')->nullable()->after('country');
            }
            if (! Schema::hasColumn('audit_logs', 'correlation_id')) {
                $table->string('correlation_id', 64)->nullable()->after('reason');
            }
            if (! Schema::hasColumn('audit_logs', 'request_id')) {
                $table->string('request_id', 64)->nullable()->after('correlation_id');
            }
        });

        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('created_at');
                $table->index('action');
                $table->index(['model_type', 'model_id']);
                $table->index('module');
            });
        } catch (\Throwable) {
            // Indexes may already exist on some environments.
        }
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            foreach ([
                'actor_id', 'actor_type', 'module', 'user_agent', 'device',
                'browser', 'country', 'reason', 'correlation_id', 'request_id',
            ] as $column) {
                if (Schema::hasColumn('audit_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
