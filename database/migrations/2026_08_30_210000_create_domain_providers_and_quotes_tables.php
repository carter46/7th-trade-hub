<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('domain_providers')) {
            Schema::create('domain_providers', function (Blueprint $table) {
                $table->id();
                $table->string('key', 64)->unique();
                $table->string('display_name');
                $table->string('adapter_class');
                $table->boolean('enabled')->default(false);
                $table->boolean('is_default')->default(false);
                $table->unsignedSmallInteger('fallback_priority')->nullable();
                $table->boolean('sandbox')->default(true);
                $table->json('capabilities')->nullable();
                $table->text('credentials')->nullable();
                $table->string('health_status', 32)->default('unknown');
                $table->timestamp('last_health_check_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('domain_quotes')) {
            Schema::create('domain_quotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('platform_product_id')->constrained('platform_products')->cascadeOnDelete();
                $table->string('provider_key', 64);
                $table->string('token_hash', 64)->unique();
                $table->string('fqdn');
                $table->string('tld', 64);
                $table->string('sld', 255);
                $table->decimal('provider_cost', 18, 4);
                $table->string('provider_currency', 8)->default('USD');
                $table->decimal('retail_price', 18, 2);
                $table->string('retail_currency', 8)->default('NGN');
                $table->boolean('premium')->default(false);
                $table->string('purchase_type', 32)->default('registration');
                $table->json('provider_meta')->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('consumed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_quotes');
        Schema::dropIfExists('domain_providers');
    }
};
