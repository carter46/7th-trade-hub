<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 32)->unique(); // e.g. TRD-90421
            $table->string('type', 40); // crypto_purchase, service, hosting, etc.
            $table->string('label', 120); // display name e.g. "Bitcoin Purchase"
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('asset_type', 20)->nullable(); // BTC, ETH for admin
            $table->string('status', 20)->default('pending'); // pending, completed, processing, cancelled, failed
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
