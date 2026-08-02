<?php

namespace Tests\Feature\Admin;

use App\Models\ExchangeRate;
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

    public function test_index_shows_buy_rate_and_not_full_coin_as_per_dollar(): void
    {
        Http::fake([
            'api.bybit.com/*' => Http::response([
                'retCode' => 0,
                'result' => ['list' => [['lastPrice' => '100000']]],
            ], 200),
            'api.coingecko.com/*' => Http::response([
                'tether' => ['ngn' => 1420],
                'bitcoin' => ['usd' => 100000, 'ngn' => 142000000],
            ], 200),
        ]);

        ExchangeRate::query()->create([
            'asset' => 'BTC',
            'coingecko_id' => 'bitcoin',
            'bybit_symbol' => 'BTCUSDT',
            'allowed_network_ids' => ['bitcoin'],
            'sell_rate_ngn' => 1395,
            'buy_rate_ngn' => 1395,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'ETH',
            'coingecko_id' => 'ethereum',
            'allowed_network_ids' => ['ethereum'],
            'sell_rate_ngn' => 5650000,
            'buy_rate_ngn' => 5650000,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.exchange-rates'))
            ->assertOk()
            ->assertSee('Our Buy Rate', false)
            ->assertSee('Current Market', false)
            ->assertSee('₦1,395.00', false)
            ->assertSee('Needs update', false)
            ->assertDontSee('₦5,650,000.00 / $1', false);
    }

    public function test_store_rejects_full_coin_buy_rate(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.exchange-rates.store'), [
                'asset' => 'BTC',
                'coingecko_id' => 'bitcoin',
                'sell_rate_ngn' => 162000000,
                'allowed_network_ids' => ['bitcoin'],
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('sell_rate_ngn');
    }

    public function test_store_rejects_network_outside_whitelist(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.exchange-rates.store'), [
                'asset' => 'BTC',
                'coingecko_id' => 'bitcoin',
                'sell_rate_ngn' => 1400,
                'allowed_network_ids' => ['tron'],
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('allowed_network_ids');
    }

    public function test_store_persists_canonical_network_ids(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.exchange-rates.store'), [
                'asset' => 'USDT',
                'coingecko_id' => 'tether',
                'sell_rate_ngn' => 1395,
                'allowed_network_ids' => ['ethereum', 'tron'],
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.exchange-rates'));

        $row = ExchangeRate::query()->where('asset', 'USDT')->first();
        $this->assertNotNull($row);
        $this->assertEqualsCanonicalizing(['ethereum', 'tron'], $row->allowed_network_ids);
    }

    public function test_wallet_create_lists_catalog_coin_even_without_networks_by_coin_config(): void
    {
        // Simulate legacy config gap: coin has catalog networks but no networks_by_coin entry.
        config([
            'crypto.networks_by_coin' => [
                'BTC' => ['Bitcoin'],
            ],
            'crypto.network_ids_by_coin' => [
                'BTC' => ['bitcoin'],
                'USDT' => ['ethereum', 'tron'],
            ],
        ]);

        ExchangeRate::query()->create([
            'asset' => 'USDT',
            'coingecko_id' => 'tether',
            'allowed_network_ids' => ['tron', 'ethereum'],
            'sell_rate_ngn' => 1400,
            'buy_rate_ngn' => 1400,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'NOCHAIN',
            'coingecko_id' => null,
            'allowed_network_ids' => [],
            'sell_rate_ngn' => 1400,
            'buy_rate_ngn' => 1400,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.crypto-wallets.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('USDT', $html);
        $this->assertStringNotContainsString('NOCHAIN', $html);
    }

    public function test_edit_form_seeds_buy_rate_not_corrupt_full_coin(): void
    {
        Http::fake([
            'api.bybit.com/*' => Http::response([
                'retCode' => 0,
                'result' => ['list' => [['lastPrice' => '100000']]],
            ], 200),
            'api.coingecko.com/*' => Http::response([
                'tether' => ['ngn' => 1420],
                'bitcoin' => ['usd' => 100000, 'ngn' => 142000000],
            ], 200),
        ]);

        $settings = \App\Models\OtcPricingSetting::current();
        $settings->market_rate_ngn = 1420;
        $settings->cached_market_rate_ngn = 1420;
        $settings->save();

        $rate = ExchangeRate::query()->create([
            'asset' => 'BTC',
            'coingecko_id' => 'bitcoin',
            'allowed_network_ids' => ['bitcoin'],
            'sell_rate_ngn' => 162000000,
            'buy_rate_ngn' => 162000000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.exchange-rates.edit', $rate))
            ->assertOk()
            ->assertSee('Our Buy Rate', false)
            ->assertSee('Current', false)
            ->assertSee('Market USD', false)
            ->getContent();

        $this->assertStringNotContainsString('value="162000000"', $html);
        $this->assertStringNotContainsString("customerRate: 162000000", $html);
    }

    public function test_corrupt_catalog_rate_falls_back_for_quotes(): void
    {
        ExchangeRate::query()->create([
            'asset' => 'BTC',
            'coingecko_id' => 'bitcoin',
            'allowed_network_ids' => ['bitcoin'],
            'sell_rate_ngn' => 162000000,
            'buy_rate_ngn' => 162000000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Seed OTC global so fallback works.
        $settings = \App\Models\OtcPricingSetting::current();
        $settings->market_rate_ngn = 1420;
        $settings->cached_market_rate_ngn = 1420;
        $settings->manual_customer_rate_ngn = 1395;
        $settings->mode = \App\Models\OtcPricingSetting::MODE_MANUAL_CUSTOMER_RATE;
        $settings->save();

        $resolved = app(ExchangeQuoteService::class)->resolveCustomerRateForCoin('BTC');
        $this->assertLessThanOrEqual(10000, $resolved['rate']);
        $this->assertEqualsWithDelta(1395, $resolved['rate'], 0.01);
    }

    public function test_quote_for_usd_uses_buy_rate_times_usd(): void
    {
        Http::fake([
            'api.bybit.com/*' => Http::response([
                'retCode' => 0,
                'result' => ['list' => [['lastPrice' => '100']]],
            ], 200),
        ]);

        ExchangeRate::query()->create([
            'asset' => 'USDT',
            'coingecko_id' => 'tether',
            'bybit_symbol' => 'USDTUSDT',
            'allowed_network_ids' => ['tron'],
            'sell_rate_ngn' => 1395,
            'buy_rate_ngn' => 1395,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $quote = app(ExchangeQuoteService::class)->quoteForUsd('USDT', 10.0);
        $this->assertEqualsWithDelta(13950.0, $quote['expected_ngn'], 0.01);
    }
}
