<?php

namespace Tests\Feature\Admin;

use App\Models\ExchangeRate;
use App\Models\OtcPricingSetting;
use App\Models\User;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRatesBuyRateUiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    protected function setUp(): void
    {
        parent::setUp();

        OtcPricingSetting::current()->update([
            'mode' => OtcPricingSetting::MODE_LIVE_MINUS_SPREAD,
            'market_rate_ngn' => 1600,
            'cached_market_rate_ngn' => 1600,
            'spread_ngn' => 25,
        ]);
    }

    public function test_index_shows_per_coin_spread_and_buy_rate(): void
    {
        Http::fake([
            'api.bybit.com/*' => Http::response([
                'retCode' => 0,
                'result' => ['list' => [['lastPrice' => '100000']]],
            ], 200),
        ]);

        ExchangeRate::query()->create([
            'asset' => 'BTC',
            'coingecko_id' => 'bitcoin',
            'bybit_symbol' => 'BTCUSDT',
            'allowed_network_ids' => ['bitcoin'],
            'spread_ngn' => 20,
            'sell_rate_ngn' => 1580,
            'buy_rate_ngn' => 1580,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.exchange-rates'))
            ->assertOk()
            ->assertSee('Our Buy Rate', false)
            ->assertSee('₦1,580.00', false)
            ->assertSee('₦20.00', false)
            ->assertSee('Update market', false);
    }

    public function test_store_saves_coin_spread_and_calculated_buy_rate(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.exchange-rates.store'), [
                'asset' => 'BTC',
                'coingecko_id' => 'bitcoin',
                'spread_ngn' => 20,
                'allowed_network_ids' => ['bitcoin'],
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.exchange-rates'));

        $row = ExchangeRate::query()->where('asset', 'BTC')->first();
        $this->assertNotNull($row);
        $this->assertEqualsWithDelta(20, (float) $row->spread_ngn, 0.01);
        $this->assertEqualsWithDelta(1580, (float) $row->sell_rate_ngn, 0.01);
    }

    public function test_store_rejects_network_outside_whitelist(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.exchange-rates.store'), [
                'asset' => 'BTC',
                'coingecko_id' => 'bitcoin',
                'spread_ngn' => 25,
                'allowed_network_ids' => ['tron'],
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('allowed_network_ids');
    }

    public function test_edit_form_has_spread_not_manual_buy_rate_input(): void
    {
        Http::fake([
            'api.bybit.com/*' => Http::response([
                'retCode' => 0,
                'result' => ['list' => [['lastPrice' => '100000']]],
            ], 200),
        ]);

        $rate = ExchangeRate::query()->create([
            'asset' => 'BTC',
            'coingecko_id' => 'bitcoin',
            'allowed_network_ids' => ['bitcoin'],
            'spread_ngn' => 20,
            'sell_rate_ngn' => 1580,
            'buy_rate_ngn' => 1580,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.exchange-rates.edit', $rate))
            ->assertOk()
            ->assertSee('Our Spread (this coin)', false)
            ->assertSee('Configure market in OTC Pricing', false)
            ->getContent();

        $this->assertStringContainsString('name="spread_ngn"', $html);
        $this->assertStringNotContainsString('name="sell_rate_ngn"', $html);
    }

    public function test_quote_uses_coin_spread_against_market(): void
    {
        Http::fake([
            'api.bybit.com/*' => Http::response([
                'retCode' => 0,
                'result' => ['list' => [['lastPrice' => '1']]],
            ], 200),
        ]);

        ExchangeRate::query()->create([
            'asset' => 'USDT',
            'coingecko_id' => 'tether',
            'bybit_symbol' => 'USDTUSDT',
            'allowed_network_ids' => ['tron'],
            'spread_ngn' => 10,
            'sell_rate_ngn' => 1590,
            'buy_rate_ngn' => 1590,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $quote = app(ExchangeQuoteService::class)->quoteForUsd('USDT', 100.0);
        $this->assertEqualsWithDelta(159000.0, $quote['expected_ngn'], 0.01);
    }
}
