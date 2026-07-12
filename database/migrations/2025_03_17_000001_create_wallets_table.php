<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance_usd', 14, 2)->default(0);
            $table->decimal('crypto_btc', 18, 8)->default(0);
            $table->decimal('crypto_eth', 18, 8)->default(0);
            $table->string('balance_change_label', 100)->nullable(); // e.g. "+12.5% from last month"
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
