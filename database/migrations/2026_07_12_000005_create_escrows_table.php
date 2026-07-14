<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('seller_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('status', 20)->default('locked');
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 14, 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('evidence_paths')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};
