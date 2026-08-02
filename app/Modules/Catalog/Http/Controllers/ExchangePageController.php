<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\CryptoPriceService;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ExchangePageController extends Controller
{
    public function __invoke(CryptoPriceService $prices, ExchangeQuoteService $quotes): View
    {
        $catalog = ExchangeRate::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $live = $prices->liveRatesForSymbols($catalog->pluck('asset')->all());
        $fx = (float) ($quotes->resolveMarketRate()['rate'] ?? 0);

        /** @var Collection<int, object> $rates */
        $rates = $catalog->map(function (ExchangeRate $rate) use ($live, $prices, $quotes, $fx) {
            $symbol = strtoupper((string) $rate->asset);
            $row = $live[$symbol] ?? null;
            $resolved = $quotes->resolveCustomerRateForCoin($symbol);
            $buyRate = (($resolved['rate'] ?? 0) > 0 && (float) $resolved['rate'] <= ExchangeRate::maxBuyRatePerUsd())
                ? (float) $resolved['rate']
                : null;

            try {
                $coinUsd = $quotes->coinUsdPrice($symbol);
            } catch (\Throwable) {
                $coinUsd = 0.0;
            }

            $fxForCoin = ($resolved['market'] ?? 0) > 0
                ? (float) $resolved['market']
                : ($fx > 0 ? $fx : (float) ($row['ngn'] ?? 0));
            $coinNgn = ($coinUsd > 0 && $fxForCoin > 0) ? round($coinUsd * $fxForCoin, 2) : (float) ($row['coin_ngn'] ?? 0);

            return (object) [
                'asset' => $symbol,
                'sell_rate_ngn' => $buyRate ?? 0,
                'buy_rate_ngn' => $buyRate ?? 0,
                'customer_rate' => $buyRate ?? 0,
                'otc_market_rate' => $fxForCoin,
                'spread' => $resolved['spread'],
                'pricing_source' => $resolved['source'],
                'coin_usd' => $coinUsd,
                'coin_ngn' => $coinNgn > 0 ? $coinNgn : null,
                'minimum_amount' => $rate->minimum_amount,
                'maximum_amount' => $rate->maximum_amount,
                'min_amount_usd' => $rate->min_amount_usd,
                'max_amount_usd' => $rate->max_amount_usd,
                'processing_time' => $rate->processing_time,
                'market_rate_ngn' => $fxForCoin > 0 ? $fxForCoin : null,
                'change_24h' => $row['change_24h'] ?? null,
                'logo' => $rate->resolvedLogoUrl() ?? ($row['logo'] ?? $prices->logoUrl($symbol)),
                'is_live' => (bool) ($row['is_live'] ?? false),
            ];
        });

        return view('pages.exchange', [
            'rates' => $rates,
            'pricesLive' => $rates->contains(fn ($r) => $r->is_live && $r->market_rate_ngn !== null),
        ]);
    }
}
