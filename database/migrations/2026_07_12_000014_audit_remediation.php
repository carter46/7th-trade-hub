<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            if (! Schema::hasColumn('wallets', 'type')) {
                $table->string('type', 20)->default('user')->after('user_id');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite tests: add type only; platform wallet uses user_id null via raw insert in seeder
            return;
        }

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('wallet_fundings', function (Blueprint $table) {
            $table->index('status');
            $table->index('wallet_id');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('escrows', function (Blueprint $table) {
            $table->unique('order_id');
        });

        Schema::table('wallet_fundings', function (Blueprint $table) {
            $table->foreign('reversal_transaction_id')
                ->references('id')
                ->on('transactions')
                ->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('reverses_transaction_id')
                ->references('id')
                ->on('transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('wallets', function (Blueprint $table) {
                if (Schema::hasColumn('wallets', 'type')) {
                    $table->dropColumn('type');
                }
            });

            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['reverses_transaction_id']);
        });

        Schema::table('wallet_fundings', function (Blueprint $table) {
            $table->dropForeign(['reversal_transaction_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['wallet_id']);
        });

        Schema::table('escrows', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('type');
        });
    }
};
