<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $validated = $request->validate([
            'tolerance_percent' => ['required', 'numeric', 'in:0.1,0.25,0.5,1'],
            'quote_ttl_minutes' => ['required', 'integer', 'in:15,30'],
            'max_orders_per_wallet' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $before = OtcPricingSetting::current()->toArray();
        $settings = $this->quotes->updatePricing($validated);
        $this->audit->log(auth()->id(), 'otc_pricing.updated', $settings, $before, $settings->toArray(), $request->ip());

        return back()->with('status', __('OTC settings saved.'));
    }
}
