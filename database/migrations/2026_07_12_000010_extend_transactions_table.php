<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('wallet_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('wallet_funding_id')->nullable()->after('wallet_id')->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->after('wallet_funding_id')->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->foreignId('escrow_id')->nullable()->after('withdrawal_id')->constrained()->nullOnDelete();
            $table->unsignedBigInteger('reverses_transaction_id')->nullable()->after('escrow_id');
            $table->text('description')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wallet_id');
            $table->dropConstrainedForeignId('wallet_funding_id');
            $table->dropConstrainedForeignId('order_id');
            $table->dropConstrainedForeignId('withdrawal_id');
            $table->dropConstrainedForeignId('escrow_id');
            $table->dropColumn(['reverses_transaction_id', 'description']);
        });
    }
};
