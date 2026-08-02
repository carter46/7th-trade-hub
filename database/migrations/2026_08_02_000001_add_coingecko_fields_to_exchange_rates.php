<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('exchange_rates')) {
            return;
        }

        Schema::table('exchange_rates', function (Blueprint $table) {
            if (! Schema::hasColumn('exchange_rates', 'coingecko_id')) {
                $table->string('coingecko_id', 80)->nullable()->after('asset');
            }
            if (! Schema::hasColumn('exchange_rates', 'logo_url')) {
                $table->string('logo_url', 500)->nullable()->after('coingecko_id');
            }
        });

        $defaults = [
            'BTC' => [
                'coingecko_id' => 'bitcoin',
                'logo_url' => 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
            ],
            'ETH' => [
                'coingecko_id' => 'ethereum',
                'logo_url' => 'https://assets.coingecko.com/coins/images/279/large/ethereum.png',
            ],
            'USDT' => [
                'coingecko_id' => 'tether',
                'logo_url' => 'https://assets.coingecko.com/coins/images/325/large/Tether.png',
            ],
            'SOL' => [
                'coingecko_id' => 'solana',
                'logo_url' => 'https://assets.coingecko.com/coins/images/4128/large/solana.png',
            ],
            'BNB' => [
                'coingecko_id' => 'binancecoin',
                'logo_url' => 'https://assets.coingecko.com/coins/images/825/large/bnb-icon2_2x.png',
            ],
        ];

        foreach ($defaults as $asset => $meta) {
            DB::table('exchange_rates')
                ->whereRaw('UPPER(asset) = ?', [$asset])
                ->where(function ($q) {
                    $q->whereNull('coingecko_id')->orWhere('coingecko_id', '');
                })
                ->update($meta);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('exchange_rates')) {
            return;
        }

        Schema::table('exchange_rates', function (Blueprint $table) {
            if (Schema::hasColumn('exchange_rates', 'logo_url')) {
                $table->dropColumn('logo_url');
            }
            if (Schema::hasColumn('exchange_rates', 'coingecko_id')) {
                $table->dropColumn('coingecko_id');
            }
        });
    }
};
