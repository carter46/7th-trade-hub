<?php

namespace Tests\Feature\Admin;

use App\Models\ExchangeRate;
use App\Models\OtcPricingSetting;
use App\Models\User;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtcPricingSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_otc_pricing_page_shows_market_field(): void
    {
        OtcPricingSetting::current()->update([
            'market_rate_ngn' => 1600,
            'spread_ngn' => 25,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.otc-pricing'))
            ->assertOk()
            ->assertSee('Current Market USD→NGN', false)
            ->assertSee('Default spread for new coins', false);
    }

    public function test_updating_market_recalculates_each_coin_from_its_spread(): void
    {
        ExchangeRate::query()->create([
            'asset' => 'BTC',
            'coingecko_id' => 'bitcoin',
            'allowed_network_ids' => ['bitcoin'],
            'spread_ngn' => 20,
            'sell_rate_ngn' => 1580,
            'buy_rate_ngn' => 1580,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'USDT',
            'coingecko_id' => 'tether',
            'allowed_network_ids' => ['tron'],
            'spread_ngn' => 10,
            'sell_rate_ngn' => 1590,
            'buy_rate_ngn' => 1590,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.otc-pricing.update'), [
                'market_rate_ngn' => 1650,
                'spread_ngn' => 25,
                'tolerance_percent' => 0.5,
                'quote_ttl_minutes' => 15,
                'max_orders_per_wallet' => 8,
            ])
            ->assertRedirect();

        $this->assertEqualsWithDelta(1650, (float) OtcPricingSetting::current()->fresh()->market_rate_ngn, 0.01);

        $btc = ExchangeRate::query()->where('asset', 'BTC')->first();
        $usdt = ExchangeRate::query()->where('asset', 'USDT')->first();
        $this->assertEqualsWithDelta(1630, (float) $btc->sell_rate_ngn, 0.01); // 1650 - 20
        $this->assertEqualsWithDelta(1640, (float) $usdt->sell_rate_ngn, 0.01); // 1650 - 10
        $this->assertEqualsWithDelta(20, (float) $btc->spread_ngn, 0.01);
        $this->assertEqualsWithDelta(10, (float) $usdt->spread_ngn, 0.01);
    }

    public function test_coins_use_own_spread_against_global_market(): void
    {
        OtcPricingSetting::current()->update([
            'market_rate_ngn' => 1600,
            'spread_ngn' => 25,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'BTC',
            'spread_ngn' => 20,
            'sell_rate_ngn' => 1580,
            'buy_rate_ngn' => 1580,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ExchangeRate::query()->create([
            'asset' => 'SOL',
            'spread_ngn' => 40,
            'sell_rate_ngn' => 1560,
            'buy_rate_ngn' => 1560,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $quotes = app(ExchangeQuoteService::class);
        $this->assertEqualsWithDelta(1580, $quotes->resolveCustomerRateForCoin('BTC')['rate'], 0.01);
        $this->assertEqualsWithDelta(1560, $quotes->resolveCustomerRateForCoin('SOL')['rate'], 0.01);
    }
}
