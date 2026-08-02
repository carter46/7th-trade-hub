<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\CryptoPriceService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ExchangePageController extends Controller
{
    public function __invoke(CryptoPriceService $prices): View
    {
        $catalog = ExchangeRate::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $live = $prices->liveRatesForSymbols($catalog->pluck('asset')->all());

        /** @var Collection<int, object> $rates */
        $rates = $catalog->map(function (ExchangeRate $rate) use ($live, $prices) {
            $symbol = strtoupper((string) $rate->asset);
            $row = $live[$symbol] ?? null;
            $marketNgn = (float) ($row['ngn'] ?? 0);

            return (object) [
                'asset' => $symbol,
                // Admin-configured platform sell rate — what public/users see & calculate with.
                'sell_rate_ngn' => (float) $rate->sell_rate_ngn,
                'buy_rate_ngn' => (float) $rate->buy_rate_ngn,
                'minimum_amount' => $rate->minimum_amount,
                'maximum_amount' => $rate->maximum_amount,
                'processing_time' => $rate->processing_time,
                // CoinGecko live market reference (not the platform payout rate).
                'market_rate_ngn' => $marketNgn > 0 ? $marketNgn : null,
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
