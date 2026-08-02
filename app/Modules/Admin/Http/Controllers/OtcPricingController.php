<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\OtcPricingSetting;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtcPricingController extends Controller
{
    public function __construct(
        private ExchangeQuoteService $quotes,
        private AuditLogService $audit,
    ) {}

    public function edit(): View
    {
        return view('dashboard.admin.otc-pricing.edit', [
            'settings' => OtcPricingSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $maxBuy = ExchangeRate::maxBuyRatePerUsd();

        $validated = $request->validate([
            'market_rate_ngn' => ['required', 'numeric', 'min:1', 'max:'.$maxBuy],
            'spread_ngn' => ['required', 'numeric', 'min:0', 'max:1000'],
            'tolerance_percent' => ['required', 'numeric', 'in:0.1,0.25,0.5,1'],
            'quote_ttl_minutes' => ['required', 'integer', 'in:15,30'],
            'max_orders_per_wallet' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'market_rate_ngn.max' => 'Market USD→NGN must be ₦ per $1 (max ₦'.number_format($maxBuy, 0).').',
        ]);

        $market = (float) $validated['market_rate_ngn'];
        $defaultSpread = (float) $validated['spread_ngn'];
        if ($defaultSpread >= $market) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'spread_ngn' => 'Default spread must be less than the market USD→NGN rate.',
            ]);
        }

        $before = OtcPricingSetting::current()->toArray();
        $settings = $this->quotes->updatePricing([
            'mode' => OtcPricingSetting::MODE_LIVE_MINUS_SPREAD,
            'market_provider' => 'manual_reference',
            'market_rate_ngn' => $market,
            'spread_ngn' => $defaultSpread,
            'tolerance_percent' => $validated['tolerance_percent'],
            'quote_ttl_minutes' => $validated['quote_ttl_minutes'],
            'max_orders_per_wallet' => $validated['max_orders_per_wallet'],
            'last_source' => 'manual_reference',
        ]);

        // Recalculate every coin: buy = new market − that coin's own spread.
        $this->quotes->syncCatalogBuyRatesFromMarket($settings);
        $this->audit->log(auth()->id(), 'otc_pricing.updated', $settings, $before, $settings->toArray(), $request->ip());

        return back()->with('status', __('OTC market updated to ₦:market / $1. Each coin’s buy rate is market minus its own spread.', [
            'market' => number_format($market, 2),
        ]));
    }
}
