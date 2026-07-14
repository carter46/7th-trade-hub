<?php

namespace App\Modules\Wallet\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoPriceService
{
    private const CACHE_KEY = 'crypto_prices_ngn';

    private const CACHE_TTL = 60;

    public function getPrices(array $coins = ['bitcoin', 'ethereum', 'tether', 'solana', 'binancecoin']): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () use ($coins) {
            try {
                $ids = implode(',', $coins);
                $response = Http::timeout(5)->get('https://api.coingecko.com/api/v3/simple/price', [
                    'ids' => $ids,
                    'vs_currencies' => 'ngn',
                ]);

                if ($response->successful()) {
                    return $response->json();
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

    public function quoteNgn(string $coin, float $amountCrypto): array
    {
        $map = ['BTC' => 'bitcoin', 'ETH' => 'ethereum', 'USDT' => 'tether', 'SOL' => 'solana', 'BNB' => 'binancecoin'];
        $id = $map[strtoupper($coin)] ?? strtolower($coin);
        $prices = $this->getPrices([$id]);
        $rate = (float) ($prices[$id]['ngn'] ?? 0);
        $usedFallback = Cache::get(self::CACHE_KEY.'_fallback', false);

        if ($rate <= 0) {
            throw new \RuntimeException('Unable to fetch crypto rate.');
        }

        if ($usedFallback) {
            Log::channel('financial')->warning('Crypto quote used fallback rate', ['coin' => $coin]);
        }

        return [
            'rate' => $rate,
            'expected_ngn' => round($amountCrypto * $rate, 2),
            'quoted_at' => now(),
            'expires_at' => now()->addMinutes(15),
        ];
    }

    private function fallbackPrices(): array
    {
        Cache::put(self::CACHE_KEY.'_fallback', true, self::CACHE_TTL);

        return [
            'bitcoin' => ['ngn' => 64231500],
            'ethereum' => ['ngn' => 3452120],
            'tether' => ['ngn' => 1500],
            'solana' => ['ngn' => 142880],
            'binancecoin' => ['ngn' => 582400],
        ];
    }
}
