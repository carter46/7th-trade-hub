<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fingerprint audit fixes: wider crypto amount precision + USDC logo seed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasTable('crypto_sell_requests') && Schema::hasColumn('crypto_sell_requests', 'amount_crypto')) {
                DB::statement('ALTER TABLE crypto_sell_requests MODIFY amount_crypto DECIMAL(28, 10) NOT NULL');
            }
            if (Schema::hasTable('incoming_crypto_transactions') && Schema::hasColumn('incoming_crypto_transactions', 'amount')) {
                DB::statement('ALTER TABLE incoming_crypto_transactions MODIFY amount DECIMAL(28, 10) NOT NULL');
            }
        }

        if (Schema::hasTable('exchange_rates') && Schema::hasColumn('exchange_rates', 'coingecko_id')) {
            $exists = DB::table('exchange_rates')
                ->whereRaw('UPPER(asset) = ?', ['USDC'])
                ->exists();
            if ($exists) {
                DB::table('exchange_rates')
                    ->whereRaw('UPPER(asset) = ?', ['USDC'])
                    ->where(function ($q) {
                        $q->whereNull('coingecko_id')->orWhere('coingecko_id', '');
                    })
                    ->update([
                        'coingecko_id' => 'usd-coin',
                        'logo_url' => 'https://assets.coingecko.com/coins/images/6319/large/usdc.png',
                    ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }
        if (Schema::hasTable('crypto_sell_requests') && Schema::hasColumn('crypto_sell_requests', 'amount_crypto')) {
            DB::statement('ALTER TABLE crypto_sell_requests MODIFY amount_crypto DECIMAL(18, 8) NOT NULL');
        }
    }
};
