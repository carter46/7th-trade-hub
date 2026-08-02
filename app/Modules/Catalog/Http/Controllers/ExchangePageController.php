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

        $customer = $quotes->resolveCustomerRate();
        $live = $prices->liveRatesForSymbols($catalog->pluck('asset')->all());

        /** @var Collection<int, object> $rates */
        $rates = $catalog->map(function (ExchangeRate $rate) use ($live, $prices, $customer, $quotes) {
            $symbol = strtoupper((string) $rate->asset);
            $row = $live[$symbol] ?? null;
            $marketNgn = (float) ($row['ngn'] ?? 0);
            try {
                $coinUsd = $quotes->coinUsdPrice($symbol);
            } catch (\Throwable) {
                $coinUsd = 0.0;
            }

            return (object) [
                'asset' => $symbol,
                'sell_rate_ngn' => $customer['rate'] > 0 ? $customer['rate'] : (float) $rate->sell_rate_ngn,
                'buy_rate_ngn' => (float) $rate->buy_rate_ngn,
                'customer_rate' => $customer['rate'],
                'otc_market_rate' => $customer['market'],
                'spread' => $customer['spread'],
                'coin_usd' => $coinUsd,
                'minimum_amount' => $rate->minimum_amount,
                'maximum_amount' => $rate->maximum_amount,
                'min_amount_usd' => $rate->min_amount_usd,
                'max_amount_usd' => $rate->max_amount_usd,
                'processing_time' => $rate->processing_time,
                'market_rate_ngn' => $marketNgn > 0 ? $marketNgn : null,
                'change_24h' => $row['change_24h'] ?? null,
                'logo' => $rate->resolvedLogoUrl() ?? ($row['logo'] ?? $prices->logoUrl($symbol)),
                'is_live' => (bool) ($row['is_live'] ?? false),
            ];
        });

        return view('pages.exchange', [
            'rates' => $rates,
            'customerRate' => $customer,
            'pricesLive' => $rates->contains(fn ($r) => $r->is_live && $r->market_rate_ngn !== null),
        ]);
    }
}
