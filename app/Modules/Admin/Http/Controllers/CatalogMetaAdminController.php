<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\CryptoPriceService;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use App\Modules\Wallet\Services\NetworkRegistry;
use App\Modules\Wallet\Services\WalletAllocationService;
use App\Support\SortOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogMetaAdminController extends Controller
{
    public function __construct(
        private ExchangeQuoteService $quotes,
        private WalletAllocationService $allocation,
        private NetworkRegistry $networks,
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
        $marketInfo = $this->quotes->resolveMarketRate();
        $usdNgn = (float) ($marketInfo['rate'] ?? 0);
        if ($usdNgn <= 0) {
            $usdNgn = $this->usdNgnReference();
        }
        $defaultSpread = (float) (\App\Models\OtcPricingSetting::current()->spread_ngn ?? ExchangeRate::defaultSpreadNgn());

        $paginator = ExchangeRate::query()->orderBy('sort_order')->orderBy('asset')->paginate(20);

        $marketByAsset = [];
        foreach ($paginator as $rate) {
            $symbol = strtoupper((string) $rate->asset);
            $coinUsd = $this->safeCoinUsd($symbol);
            $spread = $rate->resolvedSpreadNgn($defaultSpread);
            $marketByAsset[$rate->id] = [
                'coin_usd' => $coinUsd,
                'coin_ngn' => ($coinUsd > 0 && $usdNgn > 0) ? round($coinUsd * $usdNgn, 2) : null,
                'spread' => $spread,
                'buy_rate' => $rate->calculatedBuyRatePerUsd($usdNgn, $defaultSpread),
            ];
        }

        return view('dashboard.admin.exchange-rates.index', [
            'rates' => $paginator,
            'marketByAsset' => $marketByAsset,
            'usdNgnReference' => $usdNgn,
            'defaultSpread' => $defaultSpread,
            'otcSettingsUrl' => route('admin.otc-pricing'),
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
        $spread = (float) $data['spread_ngn'];
        $buyRate = $this->buyRateForSpread($spread);

        $payload = [
            'asset' => $asset,
            'coingecko_id' => $data['coingecko_id'] ?? null,
            'bybit_symbol' => $this->resolveBybitSymbol($asset),
            'logo_url' => $data['logo_url'] ?? null,
            'buy_rate_ngn' => $buyRate,
            'sell_rate_ngn' => $buyRate,
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'maximum_amount' => $data['maximum_amount'] ?? null,
            'min_amount_usd' => $data['min_amount_usd'] ?? null,
            'max_amount_usd' => $data['max_amount_usd'] ?? null,
            'processing_time' => $data['processing_time'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => SortOrder::next(ExchangeRate::class),
        ];
        if (Schema::hasColumn('exchange_rates', 'spread_ngn')) {
            $payload['spread_ngn'] = $spread;
        }
        if (Schema::hasColumn('exchange_rates', 'allowed_network_ids')) {
            $payload['allowed_network_ids'] = $data['allowed_network_ids'];
        }
        if (Schema::hasColumn('exchange_rates', 'preferred_network_id')) {
            $payload['preferred_network_id'] = $data['preferred_network_id'] ?? null;
        }

        ExchangeRate::create($payload);

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
        $spread = (float) $data['spread_ngn'];
        $buyRate = $this->buyRateForSpread($spread);

        $payload = [
            'asset' => $asset,
            'coingecko_id' => $data['coingecko_id'] ?? null,
            'bybit_symbol' => $this->resolveBybitSymbol($asset, $exchangeRate->bybit_symbol),
            'logo_url' => $data['logo_url'] ?? null,
            'buy_rate_ngn' => $buyRate,
            'sell_rate_ngn' => $buyRate,
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'maximum_amount' => $data['maximum_amount'] ?? null,
            'min_amount_usd' => $data['min_amount_usd'] ?? null,
            'max_amount_usd' => $data['max_amount_usd'] ?? null,
            'processing_time' => $data['processing_time'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
        if (Schema::hasColumn('exchange_rates', 'spread_ngn')) {
            $payload['spread_ngn'] = $spread;
        }
        if (Schema::hasColumn('exchange_rates', 'allowed_network_ids')) {
            $payload['allowed_network_ids'] = $data['allowed_network_ids'];
        }
        if (Schema::hasColumn('exchange_rates', 'preferred_network_id')) {
            $payload['preferred_network_id'] = $data['preferred_network_id'] ?? null;
        }

        $exchangeRate->update($payload);

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
        $marketInfo = $this->quotes->resolveMarketRate();
        $usdNgn = (float) ($marketInfo['rate'] ?? 0);
        if ($usdNgn <= 0) {
            $usdNgn = $this->usdNgnReference();
        }
        $defaultSpread = (float) (\App\Models\OtcPricingSetting::current()->spread_ngn ?? ExchangeRate::defaultSpreadNgn());
        $coinSpread = $rate
            ? $rate->resolvedSpreadNgn($defaultSpread)
            : $defaultSpread;
        $symbol = strtoupper((string) ($rate?->asset ?? ''));
        $coinUsd = $symbol !== '' ? $this->safeCoinUsd($symbol) : 0.0;

        $suggestByCoin = [];
        foreach (config('crypto.suggest_network_ids_by_coin', []) as $coin => $ids) {
            $suggestByCoin[strtoupper((string) $coin)] = array_values(array_map(
                fn ($id) => $this->networks->resolveId((string) $id),
                is_array($ids) ? $ids : []
            ));
        }

        // Soft suggests only for new coins; respect an explicitly saved empty list on edit.
        $selectedIds = $rate
            ? $rate->resolvedNetworkIds()
            : ($suggestByCoin[$symbol] ?? []);

        return [
            'coins' => $prices->marketCatalog(),
            'usdNgnReference' => $usdNgn,
            'defaultSpread' => $defaultSpread,
            'coinSpread' => $coinSpread,
            'calculatedBuyRate' => $usdNgn > 0 ? max(0, round($usdNgn - $coinSpread, 2)) : null,
            'initialCoinUsd' => $coinUsd,
            'initialCoinNgn' => ($coinUsd > 0 && $usdNgn > 0) ? round($coinUsd * $usdNgn, 2) : null,
            'registryNetworks' => $this->networks->checkboxOptions(),
            'suggestNetworkIdsByCoin' => $suggestByCoin,
            'selectedNetworkIds' => $selectedIds,
            'preferredNetworkId' => $rate?->preferred_network_id
                ? $this->networks->resolveId((string) $rate->preferred_network_id)
                : ($selectedIds[0] ?? null),
            'coinMarketUrl' => route('admin.exchange-rates.coin-market'),
            'otcSettingsUrl' => route('admin.otc-pricing'),
        ];
    }

    private function buyRateForSpread(float $spread): float
    {
        $market = (float) ($this->quotes->resolveMarketRate()['rate'] ?? 0);
        if ($market <= 0) {
            return 0.0;
        }

        $rate = max(0, round($market - max(0, $spread), 2));
        if ($rate > ExchangeRate::maxBuyRatePerUsd()) {
            return 0.0;
        }

        return $rate;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedExchangeRate(Request $request, ?ExchangeRate $exchangeRate = null): array
    {
        $market = (float) ($this->quotes->resolveMarketRate()['rate'] ?? 0);

        $validated = $request->validate([
            'asset' => [
                'required',
                'string',
                'max:20',
                Rule::unique('exchange_rates', 'asset')->ignore($exchangeRate?->id),
            ],
            'coingecko_id' => ['nullable', 'string', 'max:80'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'spread_ngn' => ['required', 'numeric', 'min:0', 'max:1000'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'min_amount_usd' => ['nullable', 'numeric', 'min:0'],
            'max_amount_usd' => ['nullable', 'numeric', 'min:0'],
            'processing_time' => ['nullable', 'string', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'allowed_network_ids' => ['nullable', 'array'],
            'allowed_network_ids.*' => ['string', 'max:40'],
            'preferred_network_id' => ['nullable', 'string', 'max:40'],
        ]);

        if ($market > 0 && (float) $validated['spread_ngn'] >= $market) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'spread_ngn' => 'Coin spread must be less than the OTC market USD→NGN rate (₦'.number_format($market, 2).').',
            ]);
        }

        $asset = strtoupper($validated['asset']);
        $registryIds = $this->networks->ids();
        $requested = [];
        foreach ($validated['allowed_network_ids'] ?? [] as $id) {
            if (! is_string($id) || $id === '') {
                continue;
            }
            $resolved = $this->networks->resolveId($id);
            if (! in_array($resolved, $registryIds, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'allowed_network_ids' => 'Unknown network selected.',
                ]);
            }
            $requested[] = $resolved;
        }
        $requested = array_values(array_unique($requested));

        $preferred = isset($validated['preferred_network_id']) && $validated['preferred_network_id'] !== ''
            ? $this->networks->resolveId((string) $validated['preferred_network_id'])
            : null;
        if ($preferred && ! in_array($preferred, $requested, true)) {
            $preferred = $requested[0] ?? null;
        }
        if (! $preferred && $requested !== []) {
            $preferred = $requested[0];
        }

        $monitorable = array_values(array_filter($requested, fn ($id) => $this->networks->isMonitorable($id)));
        $wantsActive = $request->boolean('is_active', true);
        if ($wantsActive && $monitorable === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'allowed_network_ids' => $asset.' cannot be enabled for OTC deposits. Select at least one network with a blockchain monitor (or leave the coin inactive).',
                'is_active' => 'Activate requires at least one monitorable deposit network.',
            ]);
        }

        $validated['allowed_network_ids'] = $requested;
        $validated['preferred_network_id'] = $preferred;
        $validated['is_active'] = $wantsActive && $monitorable !== [];
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
