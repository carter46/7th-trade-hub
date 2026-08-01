<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_delivery_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('correlation_id')->index();
            $table->string('provider', 40)->index();
            $table->boolean('success')->default(false);
            $table->string('recipient', 255)->nullable()->index();
            $table->string('subject', 255)->nullable();
            $table->string('template_key', 120)->nullable()->index();
            $table->string('purpose', 80)->nullable()->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('provider_error_code', 120)->nullable();
            $table->text('error_message')->nullable();
            $table->text('response_body')->nullable();
            $table->string('message_id', 255)->nullable()->index();
            $table->string('request_id', 255)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('delivery_status', 40)->nullable()->index();
            $table->boolean('is_fallback')->default(false);
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_delivery_attempts');
    }
};
