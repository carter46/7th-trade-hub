<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_integrations')) {
            Schema::create('site_integrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('platform_product_id')->constrained('platform_products')->cascadeOnDelete();
                $table->string('name');
                $table->string('base_url');
                $table->string('demo_user_email')->nullable();
                $table->string('demo_admin_email')->nullable();
                $table->uuid('integration_id')->unique();
                $table->string('client_id');
                $table->text('client_secret');
                $table->text('webhook_secret');
                $table->json('capabilities')->nullable();
                $table->string('status', 32)->default('draft'); // draft|active|disabled
                $table->string('connection_status', 32)->nullable(); // ok|error|unknown|unchecked
                $table->timestamp('last_checked_at')->nullable();
                $table->text('last_error')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->unique('platform_product_id');
            });
        }

        if (! Schema::hasTable('user_tools')) {
            Schema::create('user_tools', function (Blueprint $table) {
                $table->id();
                $table->uuid('public_id')->unique();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
                $table->foreignId('platform_product_id')->constrained('platform_products')->cascadeOnDelete();
                $table->foreignId('platform_product_variant_id')->nullable()->constrained('platform_product_variants')->nullOnDelete();
                $table->unsignedInteger('instance_sequence')->default(1);
                $table->string('display_name')->nullable();
                $table->string('status', 32)->default('pending_setup'); // pending_setup|active|suspended|expired
                $table->string('site_url')->nullable();
                $table->string('admin_login_url')->nullable();
                $table->string('admin_email')->nullable();
                $table->text('admin_password')->nullable();
                $table->timestamp('purchased_at')->nullable();
                $table->timestamp('configured_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('duration_months')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['expires_at', 'status']);
            });
        }

        if (! Schema::hasTable('user_tool_integrations')) {
            Schema::create('user_tool_integrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_tool_id')->unique()->constrained('user_tools')->cascadeOnDelete();
                $table->uuid('integration_id')->unique();
                $table->string('client_id');
                $table->text('client_secret');
                $table->text('webhook_secret');
                $table->json('capabilities')->nullable();
                $table->string('connection_status', 32)->nullable();
                $table->timestamp('last_checked_at')->nullable();
                $table->text('last_error')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('site_integration_check_logs')) {
            Schema::create('site_integration_check_logs', function (Blueprint $table) {
                $table->id();
                $table->string('owner_type', 32); // demo|owned
                $table->unsignedBigInteger('owner_id');
                $table->string('direction', 32)->default('hub_to_site');
                $table->boolean('ok')->default(false);
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->string('message')->nullable();
                $table->json('payload_summary')->nullable();
                $table->timestamps();

                $table->index(['owner_type', 'owner_id']);
            });
        }

        if (! Schema::hasTable('site_launch_tokens')) {
            Schema::create('site_launch_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('token_hash', 64)->unique();
                $table->string('context', 32); // demo|owned_tool
                $table->string('role', 16); // user|admin
                $table->string('integration_id');
                $table->string('bound_email');
                $table->foreignId('hub_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('site_integration_id')->nullable()->constrained('site_integrations')->nullOnDelete();
                $table->foreignId('user_tool_id')->nullable()->constrained('user_tools')->nullOnDelete();
                $table->string('request_id')->nullable();
                $table->string('nonce', 64)->unique();
                $table->timestamp('expires_at');
                $table->timestamp('consumed_at')->nullable();
                $table->string('ip', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['expires_at', 'consumed_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_launch_tokens');
        Schema::dropIfExists('site_integration_check_logs');
        Schema::dropIfExists('user_tool_integrations');
        Schema::dropIfExists('user_tools');
        Schema::dropIfExists('site_integrations');
    }
};
