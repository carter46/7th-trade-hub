<?php

namespace App\Modules\Wallet\Services;

use App\Models\ExchangeRate;
use App\Models\ExchangeRateHistory;
use App\Models\OtcPricingSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ExchangeQuoteService
{
    public function __construct(
        private CryptoPriceService $cryptoPriceService
    ) {}

    /**
     * USD-first immutable quote for a sell order.
     *
     * @return array{
     *   amount_usd: float,
     *   amount_crypto: float,
     *   coin_usd_price: float,
     *   market_rate_ngn: float,
     *   spread_ngn: float,
     *   quoted_rate_ngn: float,
     *   expected_ngn: float,
     *   pricing_source: string,
     *   quoted_at: \Illuminate\Support\Carbon,
     *   expires_at: \Illuminate\Support\Carbon
     * }
     */
    public function quoteForUsd(string $coin, float $amountUsd): array
    {
        if ($amountUsd <= 0) {
            throw new RuntimeException('USD amount must be positive.');
        }

        $settings = OtcPricingSetting::current();
        $coinUsd = $this->coinUsdPrice($coin);
        if ($coinUsd <= 0) {
            throw new RuntimeException('Unable to resolve coin USD price.');
        }

        $customer = $this->resolveCustomerRateForCoin($coin, $settings);
        if ($customer['rate'] <= 0) {
            throw new RuntimeException('Unable to resolve customer NGN rate. Set the coin rate in Coin Catalog or OTC Pricing.');
        }

        $amountCrypto = $amountUsd / $coinUsd;
        $ttl = (int) ($settings->quote_ttl_minutes ?: config('wallet.crypto_quote_minutes', 15));
        $precision = (int) (config('crypto.amount_precision.'.strtoupper($coin)) ?? 8);

        return [
            'amount_usd' => round($amountUsd, 4),
            'amount_crypto' => (float) bcadd(sprintf('%.20F', $amountCrypto), '0', $precision),
            'coin_usd_price' => round($coinUsd, 4),
            'market_rate_ngn' => round($customer['market'], 4),
            'spread_ngn' => round($customer['spread'], 4),
            'quoted_rate_ngn' => round($customer['rate'], 4),
            'expected_ngn' => round($amountUsd * $customer['rate'], 2),
            'pricing_source' => $customer['source'],
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes($ttl),
        ];
    }

    /**
     * Preview calculator payload (non-persisted).
     *
     * @return array{customer_rate: float, market_rate: float, spread: float, expected_ngn: float, amount_crypto: float, coin_usd: float, source: string, ttl_minutes: int}
     */
    public function preview(string $coin, float $amountUsd): array
    {
        $quote = $this->quoteForUsd($coin, $amountUsd);
        $settings = OtcPricingSetting::current();

        return [
            'customer_rate' => $quote['quoted_rate_ngn'],
            'market_rate' => $quote['market_rate_ngn'],
            'spread' => $quote['spread_ngn'],
            'expected_ngn' => $quote['expected_ngn'],
            'amount_crypto' => $quote['amount_crypto'],
            'coin_usd' => $quote['coin_usd_price'],
            'source' => $quote['pricing_source'],
            'ttl_minutes' => (int) ($settings->quote_ttl_minutes ?: 15),
        ];
    }

    public function coinUsdPrice(string $coin): float
    {
        $symbol = strtoupper(trim($coin));
        $cacheKey = 'otc_coin_usd:'.$symbol;

        return (float) Cache::remember($cacheKey, (int) config('crypto.cache_ttl_seconds', 60), function () use ($symbol) {
            $bybit = $this->bybitSpotUsd($symbol);
            if ($bybit > 0) {
                return $bybit;
            }

            return $this->coingeckoUsd($symbol);
        });
    }

    /**
     * Per-coin customer NGN/$ rate from Coin Catalog, falling back to global OTC pricing.
     *
     * @return array{rate: float, market: float, spread: float, source: string}
     */
    public function resolveCustomerRateForCoin(string $coin, ?OtcPricingSetting $settings = null): array
    {
        $symbol = strtoupper(trim($coin));
        $settings ??= OtcPricingSetting::current();
        $global = $this->resolveCustomerRate($settings);

        if ($symbol !== '' && Schema::hasTable('exchange_rates')) {
            $row = ExchangeRate::query()
                ->whereRaw('UPPER(asset) = ?', [$symbol])
                ->where('is_active', true)
                ->first();
            $coinRate = (float) ($row?->sell_rate_ngn ?? 0);
            if ($coinRate > 0 && $coinRate <= ExchangeRate::maxBuyRatePerUsd()) {
                $market = $global['market'] > 0 ? $global['market'] : $coinRate;

                return [
                    'rate' => $coinRate,
                    'market' => $market,
                    'spread' => max(0, $market - $coinRate),
                    'source' => 'exchange_rate_catalog',
                ];
            }
        }

        return $global;
    }

    /**
     * @return array{rate: float, market: float, spread: float, source: string}
     */
    public function resolveCustomerRate(?OtcPricingSetting $settings = null): array
    {
        $settings ??= OtcPricingSetting::current();

        if ($settings->mode === OtcPricingSetting::MODE_MANUAL_CUSTOMER_RATE) {
            $rate = (float) ($settings->manual_customer_rate_ngn ?? 0);
            if ($rate <= 0) {
                return ['rate' => 0, 'market' => 0, 'spread' => 0, 'source' => 'manual_customer_rate'];
            }

            return [
                'rate' => $rate,
                'market' => $rate,
                'spread' => 0,
                'source' => 'manual_customer_rate',
            ];
        }

        $market = $this->resolveMarketRate($settings);
        if ($market['rate'] <= 0) {
            $manual = (float) ($settings->manual_customer_rate_ngn ?? 0);
            if ($manual > 0) {
                return [
                    'rate' => $manual,
                    'market' => $manual,
                    'spread' => 0,
                    'source' => 'manual_customer_rate_fallback',
                ];
            }

            return ['rate' => 0, 'market' => 0, 'spread' => 0, 'source' => 'unavailable'];
        }

        $spread = max(0, (float) $settings->spread_ngn);
        $customer = max(0, $market['rate'] - $spread);

        return [
            'rate' => $customer,
            'market' => $market['rate'],
            'spread' => $spread,
            'source' => $market['source'].'-spread',
        ];
    }

    /**
     * @return array{rate: float, source: string}
     */
    public function resolveMarketRate(?OtcPricingSetting $settings = null): array
    {
        $settings ??= OtcPricingSetting::current();

        // v1: manual_reference is primary; cached is fallback.
        $manual = (float) ($settings->market_rate_ngn ?? 0);
        if ($manual > 0) {
            return ['rate' => $manual, 'source' => 'manual_reference'];
        }

        $cached = (float) ($settings->cached_market_rate_ngn ?? 0);
        if ($cached > 0) {
            return ['rate' => $cached, 'source' => 'cached'];
        }

        return ['rate' => 0, 'source' => 'unavailable'];
    }

    public function recordHistory(?OtcPricingSetting $settings = null): void
    {
        if (! Schema::hasTable('exchange_rate_history')) {
            return;
        }

        $settings ??= OtcPricingSetting::current();
        $resolved = $this->resolveCustomerRate($settings);

        ExchangeRateHistory::query()->create([
            'market_rate_ngn' => $resolved['market'],
            'spread_ngn' => $resolved['spread'],
            'customer_rate_ngn' => $resolved['rate'],
            'source' => $resolved['source'],
            'meta' => [
                'mode' => $settings->mode,
                'provider' => $settings->market_provider,
            ],
            'recorded_at' => now(),
        ]);
    }

    public function updatePricing(array $data): OtcPricingSetting
    {
        $settings = OtcPricingSetting::current();
        $settings->fill($data);

        if (isset($data['market_rate_ngn']) && (float) $data['market_rate_ngn'] > 0) {
            $settings->cached_market_rate_ngn = $data['market_rate_ngn'];
            $settings->market_synced_at = now();
            $settings->last_source = $settings->market_provider ?: 'manual_reference';
        }

        $settings->save();
        $this->recordHistory($settings);

        return $settings->fresh();
    }

    private function bybitSpotUsd(string $symbol): float
    {
        if ($symbol === 'USDT') {
            return 1.0;
        }

        $pair = null;
        if (Schema::hasTable('exchange_rates')) {
            $row = ExchangeRate::query()
                ->whereRaw('UPPER(asset) = ?', [$symbol])
                ->first();
            $pair = $row?->bybit_symbol;
        }

        $pair ??= config('crypto.bybit_symbols.'.$symbol);
        if (! is_string($pair) || $pair === '' || $pair === 'USDTUSDT') {
            return $symbol === 'USDT' ? 1.0 : 0.0;
        }

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get(rtrim((string) config('crypto.bybit_spot_base'), '/').'/v5/market/tickers', [
                    'category' => 'spot',
                    'symbol' => $pair,
                ]);

            if (! $response->successful()) {
                return 0.0;
            }

            $list = $response->json('result.list');
            if (! is_array($list) || $list === []) {
                return 0.0;
            }

            $last = (float) ($list[0]['lastPrice'] ?? 0);

            return $last > 0 ? $last : 0.0;
        } catch (\Throwable $e) {
            Log::channel('financial')->warning('Bybit spot USD failed', [
                'symbol' => $symbol,
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    private function coingeckoUsd(string $symbol): float
    {
        if ($symbol === 'USDT') {
            return 1.0;
        }

        $id = $this->cryptoPriceService->coinIdForAsset($symbol);
        if (! $id) {
            return 0.0;
        }

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->withHeaders(['User-Agent' => '7th-trade-hub'])
                ->get(rtrim((string) config('crypto.api_base'), '/').'/simple/price', [
                    'ids' => $id,
                    'vs_currencies' => 'usd',
                ]);

            if (! $response->successful()) {
                return 0.0;
            }

            return (float) ($response->json($id.'.usd') ?? 0);
        } catch (\Throwable $e) {
            Log::channel('financial')->warning('CoinGecko USD failed', [
                'symbol' => $symbol,
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }
}
