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

        /** @var Collection<int, object> $rates */
        $rates = $catalog->map(function (ExchangeRate $rate) use ($live, $prices, $quotes) {
            $symbol = strtoupper((string) $rate->asset);
            $row = $live[$symbol] ?? null;
            $fxNgn = (float) ($row['ngn'] ?? 0);
            $resolved = $quotes->resolveCustomerRateForCoin($symbol);
            try {
                $coinUsd = $quotes->coinUsdPrice($symbol);
            } catch (\Throwable) {
                $coinUsd = 0.0;
            }

            $buyRate = $rate->effectiveBuyRatePerUsd();
            if ($buyRate === null && ($resolved['rate'] ?? 0) > 0 && (float) $resolved['rate'] <= ExchangeRate::maxBuyRatePerUsd()) {
                $buyRate = (float) $resolved['rate'];
            }

            $fx = ($resolved['market'] ?? 0) > 0 ? (float) $resolved['market'] : $fxNgn;
            $coinNgn = ($coinUsd > 0 && $fx > 0) ? round($coinUsd * $fx, 2) : (float) ($row['coin_ngn'] ?? 0);

            return (object) [
                'asset' => $symbol,
                'sell_rate_ngn' => $buyRate ?? 0,
                'buy_rate_ngn' => (float) $rate->buy_rate_ngn,
                'customer_rate' => $buyRate ?? (float) $resolved['rate'],
                'otc_market_rate' => $fx,
                'spread' => $resolved['spread'],
                'pricing_source' => $resolved['source'],
                'coin_usd' => $coinUsd,
                'coin_ngn' => $coinNgn > 0 ? $coinNgn : null,
                'minimum_amount' => $rate->minimum_amount,
                'maximum_amount' => $rate->maximum_amount,
                'min_amount_usd' => $rate->min_amount_usd,
                'max_amount_usd' => $rate->max_amount_usd,
                'processing_time' => $rate->processing_time,
                'market_rate_ngn' => $fx > 0 ? $fx : null,
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
