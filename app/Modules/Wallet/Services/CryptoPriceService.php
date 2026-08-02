<?php

namespace App\Modules\Wallet\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CryptoPriceService
{
    private const CACHE_KEY = 'crypto_prices_ngn';

    private const FALLBACK_FLAG = 'crypto_prices_ngn_fallback';

    private const MARKETS_CACHE_KEY = 'crypto_markets_catalog_ngn';

    /**
     * @param  list<string>|null  $coins  CoinGecko ids
     * @return array<string, array{ngn?: float|int, ngn_24h_change?: float|int}>
     */
    public function getPrices(?array $coins = null): array
    {
        $coins ??= array_values(array_unique(array_filter($this->assetMap())));
        sort($coins);
        $ttl = (int) config('crypto.cache_ttl_seconds', 60);
        $cacheKey = self::CACHE_KEY.':usd_ngn:'.md5(implode(',', $coins));

        return Cache::remember($cacheKey, $ttl, function () use ($coins) {
            try {
                $response = Http::timeout(8)
                    ->acceptJson()
                    ->withHeaders(['User-Agent' => '7th-trade-hub'])
                    ->get(rtrim((string) config('crypto.api_base'), '/').'/simple/price', [
                        'ids' => implode(',', $coins),
                        'vs_currencies' => 'usd,ngn',
                        'include_24hr_change' => 'true',
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (is_array($json) && $json !== []) {
                        Cache::forget(self::FALLBACK_FLAG);

                        return $json;
                    }
                }

                Log::channel('financial')->warning('Crypto price API returned non-success', [
                    'status' => $response->status(),
                ]);
            } catch (\Throwable $e) {
                Log::channel('financial')->warning('Crypto price API failed, using fallback', [
                    'error' => $e->getMessage(),
                ]);
            }

            return $this->fallbackPrices();
        });
    }

    /**
     * Top CoinGecko markets for the admin coin picker (id, symbol, name, logo, price).
     * price_ngn is NGN per $1 USD (not the full-coin NGN price).
     *
     * @return list<array{id: string, symbol: string, name: string, logo: ?string, price_ngn: ?float, price_usd: ?float, change_24h: ?float}>
     */
    public function marketCatalog(int $perPage = 100): array
    {
        $ttl = max(60, (int) config('crypto.cache_ttl_seconds', 60) * 5);

        return Cache::remember(self::MARKETS_CACHE_KEY.':per_usd_v2', $ttl, function () use ($perPage) {
            try {
                $response = Http::timeout(12)
                    ->acceptJson()
                    ->withHeaders(['User-Agent' => '7th-trade-hub'])
                    ->get(rtrim((string) config('crypto.api_base'), '/').'/coins/markets', [
                        'vs_currency' => 'usd',
                        'order' => 'market_cap_desc',
                        'per_page' => $perPage,
                        'page' => 1,
                        'sparkline' => 'false',
                        'price_change_percentage' => '24h',
                    ]);

                if (! $response->successful() || ! is_array($response->json())) {
                    return $this->fallbackMarketCatalog();
                }

                $usdNgn = $this->usdNgnMarketRate();

                return collect($response->json())
                    ->filter(fn ($row) => is_array($row) && ! empty($row['id']) && ! empty($row['symbol']))
                    ->map(fn (array $row) => [
                        'id' => (string) $row['id'],
                        'symbol' => strtoupper((string) $row['symbol']),
                        'name' => (string) ($row['name'] ?? $row['symbol']),
                        'logo' => isset($row['image']) ? (string) $row['image'] : null,
                        'price_usd' => isset($row['current_price']) ? (float) $row['current_price'] : null,
                        // Admin buy rate is NGN per $1 — use live USD→NGN, not full-coin NGN.
                        'price_ngn' => $usdNgn > 0 ? $usdNgn : null,
                        'change_24h' => isset($row['price_change_percentage_24h'])
                            ? (float) $row['price_change_percentage_24h']
                            : null,
                    ])
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                Log::channel('financial')->warning('CoinGecko markets catalog failed', [
                    'error' => $e->getMessage(),
                ]);
            }

            return $this->fallbackMarketCatalog();
        });
    }

    /**
     * Live USD→NGN reference (prefer USDT NGN price).
     */
    public function usdNgnMarketRate(): float
    {
        $ttl = (int) config('crypto.cache_ttl_seconds', 60);

        return (float) Cache::remember('crypto_usd_ngn_fx', $ttl, function () {
            try {
                $response = Http::timeout(8)
                    ->acceptJson()
                    ->withHeaders(['User-Agent' => '7th-trade-hub'])
                    ->get(rtrim((string) config('crypto.api_base'), '/').'/simple/price', [
                        'ids' => 'tether',
                        'vs_currencies' => 'ngn',
                    ]);

                if ($response->successful()) {
                    $ngn = (float) data_get($response->json(), 'tether.ngn', 0);
                    if ($ngn > 0) {
                        return round($ngn, 4);
                    }
                }
            } catch (\Throwable $e) {
                Log::channel('financial')->warning('USD/NGN FX fetch failed', [
                    'error' => $e->getMessage(),
                ]);
            }

            return 0.0;
        });
    }

    /**
     * Platform payout quote: admin sell rate is the source of truth.
     *
     * @return array{rate: float, expected_ngn: float, quoted_at: \Illuminate\Support\Carbon, expires_at: \Illuminate\Support\Carbon, source: string}
     */
    public function quoteNgn(string $coin, float $amountCrypto): array
    {
        $symbol = strtoupper(trim($coin));
        $rate = $this->platformSellRate($symbol);
        $source = 'platform';

        if ($rate <= 0) {
            $id = $this->coinIdForAsset($symbol) ?? strtolower($symbol);
            $prices = $this->getPrices([$id]);
            $rate = (float) ($prices[$id]['ngn'] ?? 0);
            $source = Cache::get(self::FALLBACK_FLAG, false) ? 'fallback' : 'coingecko';
        }

        if ($rate <= 0) {
            throw new \RuntimeException('Unable to resolve crypto sell rate.');
        }

        if ($source !== 'platform') {
            Log::channel('financial')->warning('Crypto quote used non-platform rate', [
                'coin' => $symbol,
                'source' => $source,
            ]);
        }

        return [
            'rate' => $rate,
            'expected_ngn' => round($amountCrypto * $rate, 2),
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes((int) config('wallet.crypto_quote_minutes', 15)),
            'source' => $source,
        ];
    }

    public function platformSellRate(string $asset): float
    {
        $row = $this->findActiveRate($asset);

        return $row ? (float) $row->sell_rate_ngn : 0.0;
    }

    public function findActiveRate(string $asset): ?ExchangeRate
    {
        if (! Schema::hasTable('exchange_rates')) {
            return null;
        }

        return ExchangeRate::query()
            ->active()
            ->whereRaw('UPPER(asset) = ?', [strtoupper(trim($asset))])
            ->first();
    }

    /**
     * @param  list<string>  $symbols
     * @return array<string, array{ngn: float, coin_ngn: float, change_24h: ?float, logo: ?string, coin_id: ?string, is_live: bool}>
     */
    public function liveRatesForSymbols(array $symbols): array
    {
        $wanted = [];
        foreach ($symbols as $symbol) {
            $symbol = strtoupper((string) $symbol);
            $id = $this->coinIdForAsset($symbol);
            if ($id) {
                $wanted[$symbol] = $id;
            }
        }

        if ($wanted === []) {
            return [];
        }

        $prices = $this->getPrices(array_values(array_unique($wanted)));
        $usedFallback = (bool) Cache::get(self::FALLBACK_FLAG, false);
        $usdNgnFx = $this->usdNgnMarketRate();
        $out = [];

        foreach ($wanted as $symbol => $id) {
            $coinNgn = (float) ($prices[$id]['ngn'] ?? 0);
            $coinUsd = (float) ($prices[$id]['usd'] ?? 0);
            // Prefer implied FX from this coin; fall back to USDT NGN.
            $ngnPerUsd = ($coinUsd > 0 && $coinNgn > 0)
                ? round($coinNgn / $coinUsd, 4)
                : ($usdNgnFx > 0 ? $usdNgnFx : 0.0);
            $change = $prices[$id]['ngn_24h_change'] ?? $prices[$id]['usd_24h_change'] ?? null;

            $out[$symbol] = [
                'ngn' => $ngnPerUsd,
                'coin_ngn' => $coinNgn,
                'change_24h' => is_numeric($change) ? (float) $change : null,
                'logo' => $this->logoUrl($symbol),
                'coin_id' => $id,
                'is_live' => ($ngnPerUsd > 0 || $coinNgn > 0) && ! $usedFallback,
            ];
        }

        return $out;
    }

    public function coinIdForAsset(string $asset): ?string
    {
        $map = $this->assetMap();

        return $map[strtoupper(trim($asset))] ?? null;
    }

    public function logoUrl(?string $coinIdOrAsset): ?string
    {
        if (! is_string($coinIdOrAsset) || $coinIdOrAsset === '') {
            return null;
        }

        $symbol = strtoupper(trim($coinIdOrAsset));
        $rate = $this->findActiveRate($symbol) ?? (
            Schema::hasTable('exchange_rates')
                ? ExchangeRate::query()->whereRaw('UPPER(asset) = ?', [$symbol])->first()
                : null
        );

        if ($rate?->logo_url) {
            return $rate->logo_url;
        }

        $id = $rate?->coingecko_id
            ?? $this->coinIdForAsset($symbol)
            ?? strtolower($coinIdOrAsset);

        $logo = config('crypto.logos.'.$id);

        return is_string($logo) && $logo !== '' ? $logo : null;
    }

    /**
     * Symbol => CoinGecko id from config + active/inactive admin exchange rates.
     *
     * @return array<string, string>
     */
    public function assetMap(): array
    {
        /** @var array<string, string> $map */
        $map = config('crypto.assets', []);

        if (Schema::hasTable('exchange_rates')) {
            ExchangeRate::query()
                ->whereNotNull('coingecko_id')
                ->where('coingecko_id', '!=', '')
                ->get(['asset', 'coingecko_id'])
                ->each(function (ExchangeRate $rate) use (&$map) {
                    $map[strtoupper((string) $rate->asset)] = (string) $rate->coingecko_id;
                });
        }

        return $map;
    }

    /**
     * @return list<array{id: string, symbol: string, name: string, logo: ?string, price_ngn: ?float, price_usd: ?float, change_24h: ?float}>
     */
    private function fallbackMarketCatalog(): array
    {
        $fx = $this->usdNgnMarketRate();
        $out = [];
        foreach ($this->assetMap() as $symbol => $id) {
            $out[] = [
                'id' => $id,
                'symbol' => $symbol,
                'name' => $symbol,
                'logo' => config('crypto.logos.'.$id),
                'price_usd' => null,
                'price_ngn' => $fx > 0 ? $fx : null,
                'change_24h' => null,
            ];
        }

        return $out;
    }

    /**
     * @return array<string, array{ngn: int, usd?: int}>
     */
    private function fallbackPrices(): array
    {
        Cache::put(self::FALLBACK_FLAG, true, (int) config('crypto.cache_ttl_seconds', 60));

        // Full-coin NGN kept for legacy quoteNgn fallback; liveRates divides by usd.
        return [
            'bitcoin' => ['usd' => 100000, 'ngn' => 155000000],
            'ethereum' => ['usd' => 3500, 'ngn' => 5425000],
            'tether' => ['usd' => 1, 'ngn' => 1550],
            'solana' => ['usd' => 150, 'ngn' => 232500],
            'binancecoin' => ['usd' => 600, 'ngn' => 930000],
        ];
    }
}
