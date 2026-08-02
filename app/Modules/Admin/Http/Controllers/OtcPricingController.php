<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRateHistory;
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
        $settings = OtcPricingSetting::current();
        $resolved = $this->quotes->resolveCustomerRate($settings);
        $history = ExchangeRateHistory::query()->orderByDesc('recorded_at')->limit(50)->get();

        return view('dashboard.admin.otc-pricing.edit', compact('settings', 'resolved', 'history'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:live_minus_spread,manual_customer_rate'],
            'market_provider' => ['required', 'in:manual_reference,cached,bybit_p2p,other'],
            'market_rate_ngn' => ['nullable', 'numeric', 'min:0'],
            'spread_ngn' => ['nullable', 'numeric', 'min:0'],
            'manual_customer_rate_ngn' => ['nullable', 'numeric', 'min:0'],
            'tolerance_percent' => ['required', 'numeric', 'in:0.1,0.25,0.5,1'],
            'quote_ttl_minutes' => ['required', 'integer', 'in:15,30'],
            'max_orders_per_wallet' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $before = OtcPricingSetting::current()->toArray();
        $settings = $this->quotes->updatePricing($validated);
        $this->audit->log(auth()->id(), 'otc_pricing.updated', $settings, $before, $settings->toArray(), $request->ip());

        return back()->with('status', __('OTC pricing saved.'));
    }
}
