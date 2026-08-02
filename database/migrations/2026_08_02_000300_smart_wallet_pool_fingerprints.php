<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crypto_deposit_wallets') && ! Schema::hasColumn('crypto_deposit_wallets', 'last_allocated_at')) {
            Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
                $table->timestamp('last_allocated_at')->nullable()->after('last_deposit_at');
            });
        }

        if (Schema::hasTable('crypto_sell_requests') && ! Schema::hasColumn('crypto_sell_requests', 'amount_crypto_base')) {
            Schema::table('crypto_sell_requests', function (Blueprint $table) {
                $table->decimal('amount_crypto_base', 28, 10)->nullable()->after('amount_crypto');
            });
        }

        if (Schema::hasTable('otc_pricing_settings') && ! Schema::hasColumn('otc_pricing_settings', 'max_orders_per_wallet')) {
            Schema::table('otc_pricing_settings', function (Blueprint $table) {
                $table->unsignedSmallInteger('max_orders_per_wallet')->default(8)->after('quote_ttl_minutes');
            });
        }

        if (Schema::hasTable('incoming_crypto_transactions') && ! Schema::hasColumn('incoming_crypto_transactions', 'token_contract')) {
            Schema::table('incoming_crypto_transactions', function (Blueprint $table) {
                $table->string('token_contract', 128)->nullable()->after('from_address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crypto_deposit_wallets') && Schema::hasColumn('crypto_deposit_wallets', 'last_allocated_at')) {
            Schema::table('crypto_deposit_wallets', function (Blueprint $table) {
                $table->dropColumn('last_allocated_at');
            });
        }

        if (Schema::hasTable('crypto_sell_requests') && Schema::hasColumn('crypto_sell_requests', 'amount_crypto_base')) {
            Schema::table('crypto_sell_requests', function (Blueprint $table) {
                $table->dropColumn('amount_crypto_base');
            });
        }

        if (Schema::hasTable('otc_pricing_settings') && Schema::hasColumn('otc_pricing_settings', 'max_orders_per_wallet')) {
            Schema::table('otc_pricing_settings', function (Blueprint $table) {
                $table->dropColumn('max_orders_per_wallet');
            });
        }

        if (Schema::hasTable('incoming_crypto_transactions') && Schema::hasColumn('incoming_crypto_transactions', 'token_contract')) {
            Schema::table('incoming_crypto_transactions', function (Blueprint $table) {
                $table->dropColumn('token_contract');
            });
        }
    }
};
