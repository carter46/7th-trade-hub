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

    public function test_live_rates_for_symbols_maps_ngn_and_logos(): void
    {
        Cache::flush();

        Http::fake([
            'api.coingecko.com/*' => Http::response([
                'bitcoin' => ['ngn' => 95000000, 'ngn_24h_change' => 1.25],
                'tether' => ['ngn' => 1550, 'ngn_24h_change' => -0.1],
            ], 200),
        ]);

        $live = app(CryptoPriceService::class)->liveRatesForSymbols(['BTC', 'USDT', 'UNKNOWN']);

        $this->assertArrayHasKey('BTC', $live);
        $this->assertArrayHasKey('USDT', $live);
        $this->assertArrayNotHasKey('UNKNOWN', $live);
        $this->assertSame(95000000.0, $live['BTC']['ngn']);
        $this->assertTrue($live['BTC']['is_live']);
        $this->assertStringContainsString('coingecko.com', (string) $live['BTC']['logo']);
    }

    public function test_market_catalog_maps_coingecko_markets_payload(): void
    {
        Cache::flush();

        Http::fake([
            'api.coingecko.com/*/coins/markets*' => Http::response([
                [
                    'id' => 'bitcoin',
                    'symbol' => 'btc',
                    'name' => 'Bitcoin',
                    'image' => 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
                    'current_price' => 95000000,
                    'price_change_percentage_24h' => 1.2,
                ],
            ], 200),
        ]);

        $catalog = app(CryptoPriceService::class)->marketCatalog();

        $this->assertNotEmpty($catalog);
        $this->assertSame('bitcoin', $catalog[0]['id']);
        $this->assertSame('BTC', $catalog[0]['symbol']);
        $this->assertSame(95000000.0, $catalog[0]['price_ngn']);
    }
}
