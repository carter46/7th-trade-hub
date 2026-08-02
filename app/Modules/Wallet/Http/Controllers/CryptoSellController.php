<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CryptoSellRequest;
use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\CryptoPriceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CryptoSellController extends Controller
{
    public function __construct(
        private CryptoPriceService $priceService
    ) {}

    public function index(): View
    {
        $requests = CryptoSellRequest::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.deposit.crypto-index', [
            'requests' => $requests,
            'wallet' => auth()->user()->wallet,
        ]);
    }

    public function create(): View
    {
        $catalog = ExchangeRate::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $rateMap = $catalog->mapWithKeys(function (ExchangeRate $rate) {
            $symbol = strtoupper((string) $rate->asset);

            return [$symbol => [
                'sell' => (float) $rate->sell_rate_ngn,
                'buy' => (float) $rate->buy_rate_ngn,
                'min' => (float) ($rate->minimum_amount ?? 0),
                'max' => (float) ($rate->maximum_amount ?? 0),
                'time' => $rate->processing_time,
                'logo' => $rate->resolvedLogoUrl(),
            ]];
        });

        return view('dashboard.user.deposit.crypto-create', [
            'wallet' => auth()->user()->wallet,
            'rateMap' => $rateMap,
            'coins' => $rateMap->keys()->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            return redirect()->route('dashboard.wallet')->with('error', __('Create a wallet first.'));
        }

        $activeAssets = ExchangeRate::query()
            ->active()
            ->pluck('asset')
            ->map(fn ($asset) => strtoupper((string) $asset))
            ->all();

        $validated = $request->validate([
            'coin' => ['required', 'string', Rule::in($activeAssets)],
            'network' => ['nullable', 'string', 'max:20'],
            'amount_crypto' => ['required', 'numeric', 'min:0.00000001'],
        ]);

        $coin = strtoupper($validated['coin']);
        $amount = (float) $validated['amount_crypto'];
        $platformRate = ExchangeRate::query()
            ->active()
            ->whereRaw('UPPER(asset) = ?', [$coin])
            ->first();

        if ($platformRate) {
            if ($platformRate->minimum_amount !== null && $amount < (float) $platformRate->minimum_amount) {
                return back()
                    ->withInput()
                    ->withErrors(['amount_crypto' => __('Minimum amount for :coin is :min.', [
                        'coin' => $coin,
                        'min' => $platformRate->minimum_amount,
                    ])]);
            }
            if ($platformRate->maximum_amount !== null && $amount > (float) $platformRate->maximum_amount) {
                return back()
                    ->withInput()
                    ->withErrors(['amount_crypto' => __('Maximum amount for :coin is :max.', [
                        'coin' => $coin,
                        'max' => $platformRate->maximum_amount,
                    ])]);
            }
        }

        $quote = $this->priceService->quoteNgn($coin, $amount);

        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => $coin,
            'network' => $validated['network'] ?? null,
            'amount_crypto' => $validated['amount_crypto'],
            'quoted_rate_ngn' => $quote['rate'],
            'expected_ngn' => $quote['expected_ngn'],
            'quoted_at' => $quote['quoted_at'],
            'expires_at' => $quote['expires_at'],
            'status' => 'pending',
            'platform_address' => config('wallet.platform_crypto_address', 'TBD-CONTACT-SUPPORT'),
        ]);

        return redirect()->route('dashboard.crypto-sell.index')
            ->with('status', __('Sell request created. Quote valid for 15 minutes. Send crypto then await admin verification.'));
    }

    public function refreshQuote(CryptoSellRequest $cryptoSellRequest): RedirectResponse
    {
        $this->authorizeRequest($cryptoSellRequest);

        if ($cryptoSellRequest->status !== 'pending') {
            return back()->with('error', __('Cannot refresh quote for this request.'));
        }

        $quote = $this->priceService->quoteNgn(
            $cryptoSellRequest->coin,
            (float) $cryptoSellRequest->amount_crypto
        );

        $cryptoSellRequest->update([
            'quoted_rate_ngn' => $quote['rate'],
            'expected_ngn' => $quote['expected_ngn'],
            'quoted_at' => $quote['quoted_at'],
            'expires_at' => $quote['expires_at'],
        ]);

        return back()->with('status', __('Quote refreshed.'));
    }

    private function authorizeRequest(CryptoSellRequest $request): void
    {
        abort_unless($request->user_id === auth()->id(), 403);
    }
}
