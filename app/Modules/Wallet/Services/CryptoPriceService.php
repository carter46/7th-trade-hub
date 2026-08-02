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

    /**
     * @param  list<string>|null  $coins  CoinGecko ids
     * @return array<string, array{ngn?: float|int, ngn_24h_change?: float|int}>
     */
    public function getPrices(?array $coins = null): array
    {
        $coins ??= array_values($this->assetMap());
        $ttl = (int) config('crypto.cache_ttl_seconds', 60);

        return Cache::remember(self::CACHE_KEY, $ttl, function () use ($coins) {
            try {
                $response = Http::timeout(8)
                    ->acceptJson()
                    ->withHeaders(['User-Agent' => '7th-trade-hub'])
                    ->get(rtrim((string) config('crypto.api_base'), '/').'/simple/price', [
                        'ids' => implode(',', $coins),
                        'vs_currencies' => 'ngn',
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
     * Platform payout quote: admin sell rate is the source of truth.
     * CoinGecko is only used when no active admin rate exists for the asset.
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

    /**
     * Admin-configured sell rate for an asset (NGN per 1 coin), or 0 if missing.
     */
    public function platformSellRate(string $asset): float
    {
        if (! Schema::hasTable('exchange_rates')) {
            return 0.0;
        }

        $row = ExchangeRate::query()
            ->active()
            ->whereRaw('UPPER(asset) = ?', [strtoupper(trim($asset))])
            ->first();

        return $row ? (float) $row->sell_rate_ngn : 0.0;
    }

    /**
     * Live NGN rates + CoinGecko logos keyed by asset symbol (BTC, ETH, …).
     *
     * @param  list<string>  $symbols
     * @return array<string, array{ngn: float, change_24h: ?float, logo: ?string, coin_id: string, is_live: bool}>
     */
    public function liveRatesForSymbols(array $symbols): array
    {
        $wanted = [];
        foreach ($symbols as $symbol) {
            $id = $this->coinIdForAsset((string) $symbol);
            if ($id) {
                $wanted[strtoupper($symbol)] = $id;
            }
        }

        if ($wanted === []) {
            return [];
        }

        $prices = $this->getPrices(array_values(array_unique($wanted)));
        $usedFallback = (bool) Cache::get(self::FALLBACK_FLAG, false);
        $out = [];

        foreach ($wanted as $symbol => $id) {
            $ngn = (float) ($prices[$id]['ngn'] ?? 0);
            $change = $prices[$id]['ngn_24h_change'] ?? null;

            $out[$symbol] = [
                'ngn' => $ngn,
                'change_24h' => is_numeric($change) ? (float) $change : null,
                'logo' => $this->logoUrl($id),
                'coin_id' => $id,
                'is_live' => $ngn > 0 && ! $usedFallback,
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

        $id = $this->coinIdForAsset($coinIdOrAsset) ?? strtolower($coinIdOrAsset);
        $logo = config('crypto.logos.'.$id);

        return is_string($logo) && $logo !== '' ? $logo : null;
    }

    /**
     * @return array<string, string>
     */
    public function assetMap(): array
    {
        /** @var array<string, string> $map */
        $map = config('crypto.assets', []);

        return $map;
    }

    /**
     * @return array<string, array{ngn: int}>
     */
    private function fallbackPrices(): array
    {
        Cache::put(self::FALLBACK_FLAG, true, (int) config('crypto.cache_ttl_seconds', 60));

        return [
            'bitcoin' => ['ngn' => 64231500],
            'ethereum' => ['ngn' => 3452120],
            'tether' => ['ngn' => 1500],
            'solana' => ['ngn' => 142880],
            'binancecoin' => ['ngn' => 582400],
        ];
    }
}
