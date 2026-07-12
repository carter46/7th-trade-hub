<?php

/**
 * Example migration: wallets table.
 * When Laravel is installed, create a new migration with:
 *   php artisan make:migration create_wallets_table
 * Then replace the up/down content with the below.
 */

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
            $table->string('currency', 20); // e.g. USD, BTC, ETH
            $table->decimal('balance', 24, 8)->default(0);
            $table->decimal('locked_balance', 24, 8)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
