<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('balance', 14, 2)->default(0)->after('user_id');
            $table->decimal('locked_balance', 14, 2)->default(0)->after('balance');
            $table->string('currency', 3)->default('NGN')->after('locked_balance');
            $table->string('gateway_subaccount_id')->nullable()->after('currency');
            $table->string('status', 20)->default('active')->after('gateway_subaccount_id');
        });

        if (Schema::hasColumn('wallets', 'balance_usd')) {
            DB::table('wallets')->update([
                'balance' => DB::raw('balance_usd'),
                'currency' => 'NGN',
            ]);
        }

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['balance_usd', 'crypto_btc', 'crypto_eth', 'balance_change_label']);
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('balance_usd', 14, 2)->default(0);
            $table->decimal('crypto_btc', 18, 8)->default(0);
            $table->decimal('crypto_eth', 18, 8)->default(0);
            $table->string('balance_change_label')->nullable();
        });

        DB::table('wallets')->update(['balance_usd' => DB::raw('balance')]);

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'balance', 'locked_balance', 'currency',
                'gateway_subaccount_id', 'status',
            ]);
        });
    }
};
