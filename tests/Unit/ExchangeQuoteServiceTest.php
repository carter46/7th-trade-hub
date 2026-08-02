<?php

namespace Tests\Unit;

use App\Models\ExchangeRate;
use App\Models\OtcPricingSetting;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeQuoteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_customer_rate_is_market_minus_default_spread(): void
    {
        OtcPricingSetting::current()->update([
            'mode' => OtcPricingSetting::MODE_LIVE_MINUS_SPREAD,
            'market_rate_ngn' => 1425,
            'spread_ngn' => 25,
            'manual_customer_rate_ngn' => null,
        ]);

        $resolved = app(ExchangeQuoteService::class)->resolveCustomerRate();

        $this->assertSame(1400.0, $resolved['rate']);
        $this->assertSame(1425.0, $resolved['market']);
        $this->assertSame(25.0, $resolved['spread']);
    }

    public function test_quote_for_usd_uses_coin_spread(): void
    {
        OtcPricingSetting::current()->update([
            'mode' => OtcPricingSetting::MODE_LIVE_MINUS_SPREAD,
            'market_rate_ngn' => 1600,
            'spread_ngn' => 25,
            'quote_ttl_minutes' => 15,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'BTC',
            'bybit_symbol' => 'BTCUSDT',
            'spread_ngn' => 20,
            'sell_rate_ngn' => 1580,
            'buy_rate_ngn' => 1580,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Http::fake([
            'api.bybit.com/*' => Http::response([
                'result' => ['list' => [['lastPrice' => '100000']]],
            ]),
            'api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 100000]]),
        ]);

        $quote = app(ExchangeQuoteService::class)->quoteForUsd('BTC', 100);

        $this->assertEquals(100.0, $quote['amount_usd']);
        $this->assertEquals(1580.0, $quote['quoted_rate_ngn']);
        $this->assertEquals(158000.0, $quote['expected_ngn']);
        $this->assertEqualsWithDelta(0.001, $quote['amount_crypto'], 0.0000001);
    }

    public function test_different_coins_can_have_different_spreads(): void
    {
        OtcPricingSetting::current()->update([
            'market_rate_ngn' => 1600,
            'spread_ngn' => 25,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'BTC',
            'spread_ngn' => 20,
            'is_active' => true,
            'sort_order' => 1,
            'sell_rate_ngn' => 1580,
            'buy_rate_ngn' => 1580,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'ETH',
            'spread_ngn' => 25,
            'is_active' => true,
            'sort_order' => 2,
            'sell_rate_ngn' => 1575,
            'buy_rate_ngn' => 1575,
        ]);

        $quotes = app(ExchangeQuoteService::class);
        $this->assertEqualsWithDelta(1580, $quotes->resolveCustomerRateForCoin('BTC')['rate'], 0.01);
        $this->assertEqualsWithDelta(1575, $quotes->resolveCustomerRateForCoin('ETH')['rate'], 0.01);
    }
}
