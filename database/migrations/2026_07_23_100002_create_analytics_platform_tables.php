<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analytics_providers')) {
            Schema::create('analytics_providers', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 60)->unique();
                $table->boolean('enabled')->default(false);
                $table->text('credentials')->nullable();
                $table->string('status', 40)->default('idle');
                $table->timestamp('last_sync_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('analytics_ga_snapshots')) {
            Schema::create('analytics_ga_snapshots', function (Blueprint $table) {
                $table->id();
                $table->string('metric', 80);
                $table->string('dimension', 120)->nullable();
                $table->date('period_start');
                $table->date('period_end');
                $table->json('payload')->nullable();
                $table->timestamp('fetched_at')->useCurrent();
                $table->timestamps();

                $table->index(['metric', 'period_start', 'period_end']);
            });
        }

        if (! Schema::hasTable('analytics_kpi_snapshots')) {
            Schema::create('analytics_kpi_snapshots', function (Blueprint $table) {
                $table->id();
                $table->string('kpi_key', 80);
                $table->string('period', 20);
                $table->decimal('value', 18, 4)->default(0);
                $table->json('meta')->nullable();
                $table->timestamp('captured_at')->useCurrent();
                $table->timestamps();

                $table->index(['kpi_key', 'period']);
            });
        }

        if (! Schema::hasTable('monitoring_heartbeats')) {
            Schema::create('monitoring_heartbeats', function (Blueprint $table) {
                $table->id();
                $table->string('key', 80)->unique();
                $table->json('payload')->nullable();
                $table->timestamp('recorded_at')->useCurrent();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('type', 60);
                $table->string('title');
                $table->text('body')->nullable();
                $table->string('action_url')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['type', 'created_at']);
            });
        }

        if (Schema::hasTable('analytics_providers')) {
            $now = now();
            foreach ([
                ['provider' => 'google_analytics', 'enabled' => false, 'status' => 'idle'],
                ['provider' => 'microsoft_clarity', 'enabled' => false, 'status' => 'idle'],
            ] as $row) {
                if (! DB::table('analytics_providers')->where('provider', $row['provider'])->exists()) {
                    DB::table('analytics_providers')->insert([
                        'provider' => $row['provider'],
                        'enabled' => $row['enabled'],
                        'status' => $row['status'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('monitoring_heartbeats');
        Schema::dropIfExists('analytics_kpi_snapshots');
        Schema::dropIfExists('analytics_ga_snapshots');
        Schema::dropIfExists('analytics_providers');
    }
};
