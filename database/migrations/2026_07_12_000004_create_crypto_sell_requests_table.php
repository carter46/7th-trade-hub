<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_sell_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('coin', 10);
            $table->string('network', 20)->nullable();
            $table->decimal('amount_crypto', 18, 8);
            $table->decimal('quoted_rate_ngn', 18, 2);
            $table->decimal('expected_ngn', 14, 2);
            $table->timestamp('quoted_at');
            $table->timestamp('expires_at');
            $table->string('status', 20)->default('pending');
            $table->string('tx_hash')->nullable();
            $table->string('platform_address')->nullable();
            $table->foreignId('wallet_funding_id')->nullable()->constrained('wallet_fundings')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_sell_requests');
    }
};
