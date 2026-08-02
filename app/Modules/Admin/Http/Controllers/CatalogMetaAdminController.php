<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\CryptoPriceService;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use App\Modules\Wallet\Services\WalletAllocationService;
use App\Support\SortOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogMetaAdminController extends Controller
{
    public function __construct(
        private ExchangeQuoteService $quotes,
        private WalletAllocationService $allocation,
    ) {}

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
        $usdNgn = $this->usdNgnReference();
        $paginator = ExchangeRate::query()->orderBy('sort_order')->orderBy('asset')->paginate(20);

        $marketByAsset = [];
        foreach ($paginator as $rate) {
            $symbol = strtoupper((string) $rate->asset);
            $coinUsd = $this->safeCoinUsd($symbol);
            $marketByAsset[$rate->id] = [
                'coin_usd' => $coinUsd,
                'coin_ngn' => ($coinUsd > 0 && $usdNgn > 0) ? round($coinUsd * $usdNgn, 2) : null,
                'buy_rate' => $rate->effectiveBuyRatePerUsd(),
                'buy_corrupt' => $rate->buyRateIsCorrupt(),
            ];
        }

        return view('dashboard.admin.exchange-rates.index', [
            'rates' => $paginator,
            'marketByAsset' => $marketByAsset,
            'usdNgnReference' => $usdNgn,
        ]);
    }

    public function createExchangeRate(CryptoPriceService $prices): View
    {
        return view('dashboard.admin.exchange-rates.create', $this->rateFormData($prices));
    }

    public function coinCatalog(CryptoPriceService $prices): JsonResponse
    {
        return response()->json([
            'coins' => $prices->marketCatalog(),
        ]);
    }

    public function coinMarket(Request $request): JsonResponse
    {
        $symbol = strtoupper(trim((string) $request->query('asset', '')));
        if ($symbol === '') {
            return response()->json(['ok' => false, 'message' => 'Asset required'], 422);
        }

        $usdNgn = $this->usdNgnReference();
        $coinUsd = $this->safeCoinUsd($symbol);

        return response()->json([
            'ok' => true,
            'asset' => $symbol,
            'coin_usd' => $coinUsd,
            'coin_ngn' => ($coinUsd > 0 && $usdNgn > 0) ? round($coinUsd * $usdNgn, 2) : null,
            'usd_ngn' => $usdNgn,
            'source' => 'Bybit Spot',
        ]);
    }

    public function storeExchangeRate(Request $request): RedirectResponse
    {
        $data = $this->validatedExchangeRate($request);
        $asset = strtoupper($data['asset']);
        $customerBuyRate = (float) ($data['sell_rate_ngn'] ?? 0);

        ExchangeRate::create([
            'asset' => $asset,
            'coingecko_id' => $data['coingecko_id'] ?? null,
            'bybit_symbol' => $this->resolveBybitSymbol($asset),
            'allowed_network_ids' => $data['allowed_network_ids'],
            'logo_url' => $data['logo_url'] ?? null,
            'buy_rate_ngn' => $customerBuyRate,
            'sell_rate_ngn' => $customerBuyRate,
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'maximum_amount' => $data['maximum_amount'] ?? null,
            'min_amount_usd' => $data['min_amount_usd'] ?? null,
            'max_amount_usd' => $data['max_amount_usd'] ?? null,
            'processing_time' => $data['processing_time'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => SortOrder::next(ExchangeRate::class),
        ]);

        return redirect()
            ->route('admin.exchange-rates')
            ->with('status', 'Exchange rate created.');
    }

    public function editExchangeRate(ExchangeRate $exchangeRate, CryptoPriceService $prices): View
    {
        return view('dashboard.admin.exchange-rates.edit', array_merge(
            $this->rateFormData($prices, $exchangeRate),
            ['rate' => $exchangeRate]
        ));
    }

    public function updateExchangeRate(Request $request, ExchangeRate $exchangeRate): RedirectResponse
    {
        $data = $this->validatedExchangeRate($request, $exchangeRate);
        $asset = strtoupper($data['asset']);
        $customerBuyRate = (float) ($data['sell_rate_ngn'] ?? 0);

        $exchangeRate->update([
            'asset' => $asset,
            'coingecko_id' => $data['coingecko_id'] ?? null,
            'bybit_symbol' => $this->resolveBybitSymbol($asset, $exchangeRate->bybit_symbol),
            'allowed_network_ids' => $data['allowed_network_ids'],
            'logo_url' => $data['logo_url'] ?? null,
            'buy_rate_ngn' => $customerBuyRate,
            'sell_rate_ngn' => $customerBuyRate,
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'maximum_amount' => $data['maximum_amount'] ?? null,
            'min_amount_usd' => $data['min_amount_usd'] ?? null,
            'max_amount_usd' => $data['max_amount_usd'] ?? null,
            'processing_time' => $data['processing_time'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
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
    private function rateFormData(CryptoPriceService $prices, ?ExchangeRate $rate = null): array
    {
        $usdNgn = $this->usdNgnReference();
        $symbol = strtoupper((string) ($rate?->asset ?? ''));
        $coinUsd = $symbol !== '' ? $this->safeCoinUsd($symbol) : 0.0;
        $buyRate = $rate?->effectiveBuyRatePerUsd();
        $defaultSpread = 25.0;
        if ($buyRate === null && $usdNgn > 0) {
            $buyRate = max(0, round($usdNgn - $defaultSpread, 2));
        }

        $networkIdsByCoin = [];
        foreach (config('crypto.network_ids_by_coin', []) as $coin => $ids) {
            $networkIdsByCoin[$coin] = collect($ids)->map(fn ($id) => [
                'id' => $id,
                'label' => $this->allocation->displayLabelForNetworkId($id),
            ])->values()->all();
        }

        return [
            'coins' => $prices->marketCatalog(),
            'usdNgnReference' => $usdNgn,
            'initialCoinUsd' => $coinUsd,
            'initialCoinNgn' => ($coinUsd > 0 && $usdNgn > 0) ? round($coinUsd * $usdNgn, 2) : null,
            'initialBuyRate' => $buyRate,
            'networkIdsByCoin' => $networkIdsByCoin,
            'selectedNetworkIds' => $rate?->resolvedNetworkIds() ?? [],
            'coinMarketUrl' => route('admin.exchange-rates.coin-market'),
            'otcSettingsUrl' => route('admin.otc-pricing'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedExchangeRate(Request $request, ?ExchangeRate $exchangeRate = null): array
    {
        $maxBuy = ExchangeRate::maxBuyRatePerUsd();

        $validated = $request->validate([
            'asset' => [
                'required',
                'string',
                'max:20',
                Rule::unique('exchange_rates', 'asset')->ignore($exchangeRate?->id),
            ],
            'coingecko_id' => ['nullable', 'string', 'max:80'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'buy_rate_ngn' => ['nullable', 'numeric', 'min:0'],
            'sell_rate_ngn' => ['required', 'numeric', 'min:0.01', 'max:'.$maxBuy],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'min_amount_usd' => ['nullable', 'numeric', 'min:0'],
            'max_amount_usd' => ['nullable', 'numeric', 'min:0'],
            'processing_time' => ['nullable', 'string', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'allowed_network_ids' => ['nullable', 'array'],
            'allowed_network_ids.*' => ['string', 'max:40'],
        ], [
            'sell_rate_ngn.max' => 'Our Buy Rate must be ₦ per $1 (max ₦'.number_format($maxBuy, 0).'). Full-coin prices are not allowed.',
        ]);

        $asset = strtoupper($validated['asset']);
        $whitelist = $this->allocation->networkIdsForCoin($asset);
        $requested = array_values(array_unique(array_map(
            'strtolower',
            array_filter($validated['allowed_network_ids'] ?? [], fn ($id) => is_string($id) && $id !== '')
        )));

        foreach ($requested as $id) {
            if (! in_array($id, $whitelist, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'allowed_network_ids' => "Network {$id} is not allowed for {$asset}.",
                ]);
            }
        }

        // Only IDs on the coin whitelist; empty is OK (buy-rate-only catalog coin).
        $validated['allowed_network_ids'] = array_values(array_intersect($whitelist, $requested));
        $validated['asset'] = $asset;

        return $validated;
    }

    private function resolveBybitSymbol(string $asset, ?string $existing = null): ?string
    {
        $fromConfig = config('crypto.bybit_symbols.'.$asset);
        if (is_string($fromConfig) && $fromConfig !== '') {
            return $fromConfig;
        }

        return $existing;
    }

    private function usdNgnReference(): float
    {
        $market = $this->quotes->resolveMarketRate();
        if (($market['rate'] ?? 0) > 0) {
            return (float) $market['rate'];
        }

        try {
            $fx = app(CryptoPriceService::class)->usdNgnMarketRate();
            if ($fx > 0) {
                return $fx;
            }
        } catch (\Throwable) {
            // ignore
        }

        return 0.0;
    }

    private function safeCoinUsd(string $coin): float
    {
        try {
            return max(0, $this->quotes->coinUsdPrice($coin));
        } catch (\Throwable) {
            return 0.0;
        }
    }
}
