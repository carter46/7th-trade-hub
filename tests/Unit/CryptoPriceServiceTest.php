<?php

namespace Tests\Unit;

use App\Modules\Wallet\Services\CryptoPriceService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CryptoPriceServiceTest extends TestCase
{
    public function test_logo_url_uses_coingecko_cdn_like_evergreen(): void
    {
        $service = app(CryptoPriceService::class);

        $this->assertSame(
            'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
            $service->logoUrl('BTC')
        );
        $this->assertSame(
            'https://assets.coingecko.com/coins/images/279/large/ethereum.png',
            $service->logoUrl('ethereum')
        );
    }

    public function test_live_rates_for_symbols_maps_ngn_per_usd(): void
    {
        Cache::flush();

        Http::fake([
            'api.coingecko.com/*' => Http::response([
                'bitcoin' => ['usd' => 100000, 'ngn' => 155000000, 'ngn_24h_change' => 1.25],
                'tether' => ['usd' => 1, 'ngn' => 1550, 'ngn_24h_change' => -0.1],
            ], 200),
        ]);

        $live = app(CryptoPriceService::class)->liveRatesForSymbols(['BTC', 'USDT', 'UNKNOWN']);

        $this->assertArrayHasKey('BTC', $live);
        $this->assertArrayHasKey('USDT', $live);
        $this->assertArrayNotHasKey('UNKNOWN', $live);
        $this->assertEqualsWithDelta(1550.0, $live['BTC']['ngn'], 0.01);
        $this->assertEqualsWithDelta(1550.0, $live['USDT']['ngn'], 0.01);
        $this->assertTrue($live['BTC']['is_live']);
        $this->assertStringContainsString('coingecko.com', (string) $live['BTC']['logo']);
    }

    public function test_market_catalog_uses_ngn_per_usd_not_full_coin_price(): void
    {
        Cache::flush();

        Http::fake([
            'api.coingecko.com/*/coins/markets*' => Http::response([
                [
                    'id' => 'bitcoin',
                    'symbol' => 'btc',
                    'name' => 'Bitcoin',
                    'image' => 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
                    'current_price' => 100000,
                    'price_change_percentage_24h' => 1.2,
                ],
            ], 200),
            'api.coingecko.com/*/simple/price*' => Http::response([
                'tether' => ['ngn' => 1550],
            ], 200),
        ]);

        $catalog = app(CryptoPriceService::class)->marketCatalog();

        $this->assertNotEmpty($catalog);
        $this->assertSame('bitcoin', $catalog[0]['id']);
        $this->assertSame('BTC', $catalog[0]['symbol']);
        $this->assertEqualsWithDelta(1550.0, (float) $catalog[0]['price_ngn'], 0.01);
        $this->assertLessThan(10000, (float) $catalog[0]['price_ngn']);
    }
}
