<?php

namespace Tests\Unit;

use App\Models\OtcPricingSetting;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeQuoteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_rate_is_market_minus_positive_spread(): void
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

    public function test_quote_for_usd_is_immutable_math(): void
    {
        OtcPricingSetting::current()->update([
            'mode' => OtcPricingSetting::MODE_LIVE_MINUS_SPREAD,
            'market_rate_ngn' => 1400,
            'spread_ngn' => 0,
            'quote_ttl_minutes' => 15,
        ]);

        Http::fake([
            'api.bybit.com/*' => Http::response([
                'result' => ['list' => [['lastPrice' => '100000']]],
            ]),
            'api.coingecko.com/*' => Http::response(['bitcoin' => ['usd' => 100000]]),
        ]);

        $quote = app(ExchangeQuoteService::class)->quoteForUsd('BTC', 500);

        $this->assertEquals(500.0, $quote['amount_usd']);
        $this->assertEquals(1400.0, $quote['quoted_rate_ngn']);
        $this->assertEquals(700000.0, $quote['expected_ngn']);
        $this->assertEqualsWithDelta(0.005, $quote['amount_crypto'], 0.0000001);
    }

    public function test_manual_customer_rate_mode_skips_spread(): void
    {
        OtcPricingSetting::current()->update([
            'mode' => OtcPricingSetting::MODE_MANUAL_CUSTOMER_RATE,
            'manual_customer_rate_ngn' => 1395,
            'market_rate_ngn' => 1425,
            'spread_ngn' => 25,
        ]);

        $resolved = app(ExchangeQuoteService::class)->resolveCustomerRate();

        $this->assertSame(1395.0, $resolved['rate']);
        $this->assertSame(0.0, $resolved['spread']);
    }
}
