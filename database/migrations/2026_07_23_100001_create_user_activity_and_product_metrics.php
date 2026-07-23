<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_activity')) {
            Schema::create('user_activity', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('action', 40)->default('viewed');
                $table->nullableMorphs('subject');
                $table->string('context_key', 120)->nullable()->index();
                $table->json('meta')->nullable();
                $table->timestamp('occurred_at')->useCurrent()->index();
                $table->timestamps();

                $table->index(['user_id', 'action', 'occurred_at']);
                $table->index(['user_id', 'subject_type', 'occurred_at']);
            });
        }

        if (! Schema::hasTable('product_metric_daily')) {
            Schema::create('product_metric_daily', function (Blueprint $table) {
                $table->id();
                $table->date('day');
                $table->string('metric_key', 80);
                $table->string('dimension', 120)->nullable();
                $table->unsignedBigInteger('count')->default(0);
                $table->timestamps();

                $table->unique(['day', 'metric_key', 'dimension'], 'product_metric_daily_unique');
                $table->index(['metric_key', 'day']);
            });
        }

        if (! Schema::hasTable('product_metric_monthly')) {
            Schema::create('product_metric_monthly', function (Blueprint $table) {
                $table->id();
                $table->char('month', 7); // YYYY-MM
                $table->string('metric_key', 80);
                $table->string('dimension', 120)->nullable();
                $table->unsignedBigInteger('count')->default(0);
                $table->timestamps();

                $table->unique(['month', 'metric_key', 'dimension'], 'product_metric_monthly_unique');
                $table->index(['metric_key', 'month']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_metric_monthly');
        Schema::dropIfExists('product_metric_daily');
        Schema::dropIfExists('user_activity');
    }
};
