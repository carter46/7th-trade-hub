<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['asset' => 'BTC', 'buy_rate_ngn' => 165000000, 'sell_rate_ngn' => 162000000, 'minimum_amount' => 0.0001, 'maximum_amount' => 2, 'processing_time' => '5–15 minutes', 'is_featured' => true, 'sort_order' => 0],
            ['asset' => 'ETH', 'buy_rate_ngn' => 5800000, 'sell_rate_ngn' => 5650000, 'minimum_amount' => 0.01, 'maximum_amount' => 50, 'processing_time' => '5–15 minutes', 'is_featured' => true, 'sort_order' => 1],
            ['asset' => 'USDT', 'buy_rate_ngn' => 1580, 'sell_rate_ngn' => 1550, 'minimum_amount' => 10, 'maximum_amount' => 100000, 'processing_time' => '5–10 minutes', 'is_featured' => true, 'sort_order' => 2],
            ['asset' => 'SOL', 'buy_rate_ngn' => 245000, 'sell_rate_ngn' => 238000, 'minimum_amount' => 0.1, 'maximum_amount' => 500, 'processing_time' => '5–15 minutes', 'is_featured' => false, 'sort_order' => 3],
            ['asset' => 'BNB', 'buy_rate_ngn' => 920000, 'sell_rate_ngn' => 900000, 'minimum_amount' => 0.05, 'maximum_amount' => 200, 'processing_time' => '5–15 minutes', 'is_featured' => false, 'sort_order' => 4],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::firstOrCreate(
                ['asset' => $rate['asset']],
                array_merge($rate, ['is_active' => true])
            );
        }
    }
}
