<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\CryptoPriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogMetaAdminController extends Controller
{
    public function platformCategories(): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function createPlatformCategory(): RedirectResponse
    {
        return redirect()->route('admin.service-categories.create');
    }

    public function storePlatformCategory(Request $request): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function editPlatformCategory($platformCategory = null): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function updatePlatformCategory(Request $request, $platformCategory = null): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function togglePlatformCategory($platformCategory = null): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function exchangeRates(): View
    {
        return view('dashboard.admin.exchange-rates.index', [
            'rates' => ExchangeRate::orderBy('sort_order')->paginate(20),
        ]);
    }

    public function createExchangeRate(CryptoPriceService $prices): View
    {
        return view('dashboard.admin.exchange-rates.create', [
            'coins' => $prices->marketCatalog(),
        ]);
    }

    public function coinCatalog(CryptoPriceService $prices): JsonResponse
    {
        return response()->json([
            'coins' => $prices->marketCatalog(),
        ]);
    }

    public function storeExchangeRate(Request $request): RedirectResponse
    {
        $data = $this->validatedExchangeRate($request);

        ExchangeRate::create([
            'asset' => strtoupper($data['asset']),
            'coingecko_id' => $data['coingecko_id'] ?? null,
            'logo_url' => $data['logo_url'] ?? null,
            'buy_rate_ngn' => $data['buy_rate_ngn'],
            'sell_rate_ngn' => $data['sell_rate_ngn'],
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'maximum_amount' => $data['maximum_amount'] ?? null,
            'processing_time' => $data['processing_time'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.exchange-rates')
            ->with('status', 'Exchange rate created.');
    }

    public function editExchangeRate(ExchangeRate $exchangeRate, CryptoPriceService $prices): View
    {
        return view('dashboard.admin.exchange-rates.edit', [
            'rate' => $exchangeRate,
            'coins' => $prices->marketCatalog(),
        ]);
    }

    public function updateExchangeRate(Request $request, ExchangeRate $exchangeRate): RedirectResponse
    {
        $data = $this->validatedExchangeRate($request, $exchangeRate);

        $exchangeRate->update([
            'asset' => strtoupper($data['asset']),
            'coingecko_id' => $data['coingecko_id'] ?? null,
            'logo_url' => $data['logo_url'] ?? null,
            'buy_rate_ngn' => $data['buy_rate_ngn'],
            'sell_rate_ngn' => $data['sell_rate_ngn'],
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'maximum_amount' => $data['maximum_amount'] ?? null,
            'processing_time' => $data['processing_time'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.exchange-rates')
            ->with('status', 'Exchange rate updated.');
    }

    public function destroyExchangeRate(ExchangeRate $exchangeRate): RedirectResponse
    {
        $exchangeRate->delete();

        return redirect()
            ->route('admin.exchange-rates')
            ->with('status', 'Rate deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedExchangeRate(Request $request, ?ExchangeRate $exchangeRate = null): array
    {
        return $request->validate([
            'asset' => [
                'required',
                'string',
                'max:20',
                Rule::unique('exchange_rates', 'asset')->ignore($exchangeRate?->id),
            ],
            'coingecko_id' => ['nullable', 'string', 'max:80'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'buy_rate_ngn' => ['required', 'numeric', 'min:0'],
            'sell_rate_ngn' => ['required', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'processing_time' => ['nullable', 'string', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
