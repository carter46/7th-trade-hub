<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CryptoDepositWallet;
use App\Models\CryptoSellRequest;
use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class CryptoSellController extends Controller
{
    public function __construct(
        private ExchangeQuoteService $quoteService,
        private \App\Modules\Wallet\Services\WalletAllocationService $allocation,
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

        $wallets = CryptoDepositWallet::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($w) => strtoupper($w->coin));

        $coins = $catalog->pluck('asset')->map(fn ($a) => strtoupper((string) $a))->unique()->values();

        // Prefer coins that have at least one active deposit wallet; fall back to catalog.
        $walletCoins = $wallets->keys();
        if ($walletCoins->isNotEmpty()) {
            $coins = $coins->filter(fn ($c) => $walletCoins->contains($c))->values();
            if ($coins->isEmpty()) {
                $coins = $walletCoins->values();
            }
        }

        $rateMap = [];
        $allowedNetworks = config('crypto.networks_by_coin', []);
        foreach ($coins as $symbol) {
            $rate = $catalog->first(fn ($r) => strtoupper((string) $r->asset) === $symbol);
            $customer = $this->quoteService->resolveCustomerRateForCoin($symbol);
            $coinWallets = $wallets[$symbol] ?? collect();
            $canonicalList = $allowedNetworks[$symbol] ?? [];
            $networks = $coinWallets
                ->groupBy(fn ($w) => $w->network)
                ->map(function ($group, $network) use ($symbol, $canonicalList) {
                    $label = $network;
                    foreach ($canonicalList as $canonical) {
                        if (strcasecmp($canonical, (string) $network) === 0) {
                            $label = $canonical;
                            break;
                        }
                    }

                    return [
                        'network' => $label,
                        'id' => $group->first()->id,
                        'confirmations' => (int) $group->min('required_confirmations'),
                        'available' => $group->where('is_active', true)->count(),
                    ];
                })
                ->values()
                ->all();

            if ($networks === [] && $canonicalList !== []) {
                continue;
            }

            try {
                $coinUsd = $this->quoteService->coinUsdPrice($symbol);
            } catch (\Throwable) {
                $coinUsd = 0;
            }

            $rateMap[$symbol] = [
                'customer_rate' => $customer['rate'],
                'market_rate' => $customer['market'],
                'spread' => $customer['spread'],
                'source' => $customer['source'],
                'coin_usd' => $coinUsd,
                'min_usd' => (float) ($rate?->min_amount_usd ?? 0),
                'max_usd' => (float) ($rate?->max_amount_usd ?? 0),
                'logo' => $rate?->resolvedLogoUrl(),
                'networks' => $networks,
            ];
        }

        return view('dashboard.user.deposit.crypto-create', [
            'wallet' => auth()->user()->wallet,
            'rateMap' => $rateMap,
            'coins' => array_keys($rateMap),
            'pricingAvailable' => collect($rateMap)->contains(fn ($r) => (float) ($r['customer_rate'] ?? 0) > 0),
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

        $walletAssets = CryptoDepositWallet::query()->active()->pluck('coin')
            ->map(fn ($c) => strtoupper((string) $c))
            ->unique()
            ->all();

        $allowed = $walletAssets !== [] ? array_values(array_intersect($activeAssets ?: $walletAssets, $walletAssets)) : $activeAssets;
        if ($allowed === []) {
            $allowed = $walletAssets !== [] ? $walletAssets : $activeAssets;
        }

        $validated = $request->validate([
            'coin' => ['required', 'string', Rule::in($allowed)],
            'network' => ['required', 'string', 'max:40'],
            'amount_usd' => ['required', 'numeric', 'min:1'],
        ]);

        $coin = strtoupper($validated['coin']);
        $amountUsd = (float) $validated['amount_usd'];

        try {
            $network = $this->allocation->canonicalizeNetwork($coin, $validated['network']);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['network' => $e->getMessage()]);
        }

        $platformRate = ExchangeRate::query()
            ->active()
            ->whereRaw('UPPER(asset) = ?', [$coin])
            ->first();

        if ($platformRate) {
            if ($platformRate->min_amount_usd !== null && $amountUsd < (float) $platformRate->min_amount_usd) {
                return back()->withInput()->withErrors([
                    'amount_usd' => __('Minimum is $:min.', ['min' => $platformRate->min_amount_usd]),
                ]);
            }
            if ($platformRate->max_amount_usd !== null && (float) $platformRate->max_amount_usd > 0
                && $amountUsd > (float) $platformRate->max_amount_usd) {
                return back()->withInput()->withErrors([
                    'amount_usd' => __('Maximum is $:max.', ['max' => $platformRate->max_amount_usd]),
                ]);
            }
        }

        try {
            $sell = DB::transaction(function () use ($user, $wallet, $coin, $network, $amountUsd) {
                $quote = $this->quoteService->quoteForUsd($coin, $amountUsd);
                $allocated = $this->allocation->allocate($coin, $network, (float) $quote['amount_crypto']);
                $depositWallet = $allocated['wallet'];

                return CryptoSellRequest::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'crypto_deposit_wallet_id' => $depositWallet->id,
                    'coin' => $coin,
                    'network' => $network,
                    'amount_crypto' => $allocated['amount_crypto'],
                    'amount_crypto_base' => $allocated['amount_crypto_base'],
                    'amount_usd' => $quote['amount_usd'],
                    'quoted_rate_ngn' => $quote['quoted_rate_ngn'],
                    'market_rate_ngn' => $quote['market_rate_ngn'],
                    'spread_ngn' => $quote['spread_ngn'],
                    'coin_usd_price' => $quote['coin_usd_price'],
                    'pricing_source' => $quote['pricing_source'],
                    'expected_ngn' => $quote['expected_ngn'],
                    'quoted_at' => $quote['quoted_at'],
                    'expires_at' => $quote['expires_at'],
                    'status' => CryptoSellRequest::STATUS_WAITING_DEPOSIT,
                    'platform_address' => $depositWallet->address,
                    'required_confirmations' => $depositWallet->required_confirmations,
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('dashboard.crypto-sell.show', $sell)
            ->with('status', __('Quote locked. Send exactly :amount :coin before it expires.', [
                'amount' => $sell->amount_crypto,
                'coin' => $coin,
            ]));
    }

    public function show(CryptoSellRequest $cryptoSellRequest): View
    {
        $this->authorizeRequest($cryptoSellRequest);

        return view('dashboard.user.deposit.crypto-show', [
            'sell' => $cryptoSellRequest,
            'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.urlencode((string) $cryptoSellRequest->platform_address),
        ]);
    }

    public function submitTx(Request $request, CryptoSellRequest $cryptoSellRequest): RedirectResponse
    {
        $this->authorizeRequest($cryptoSellRequest);

        if (! $cryptoSellRequest->isOpen()) {
            return back()->with('error', __('This order can no longer accept a transaction hash.'));
        }

        if ($cryptoSellRequest->isQuoteExpired() && $cryptoSellRequest->status === CryptoSellRequest::STATUS_WAITING_DEPOSIT) {
            return back()->with('error', __('Quote expired. Create a new quote.'));
        }

        $validated = $request->validate([
            'tx_hash' => ['required', 'string', 'max:255', 'unique:crypto_sell_requests,tx_hash'],
        ]);

        $cryptoSellRequest->update([
            'tx_hash' => $validated['tx_hash'],
            'status' => CryptoSellRequest::STATUS_SUBMITTED,
        ]);

        return back()->with('status', __('Transaction submitted. We will verify when confirmations arrive.'));
    }

    public function cancel(CryptoSellRequest $cryptoSellRequest): RedirectResponse
    {
        $this->authorizeRequest($cryptoSellRequest);

        if (! in_array($cryptoSellRequest->status, [
            CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'pending',
        ], true)) {
            return back()->with('error', __('Only waiting deposits can be cancelled.'));
        }

        $cryptoSellRequest->update(['status' => CryptoSellRequest::STATUS_CANCELLED]);

        return redirect()->route('dashboard.crypto-sell.index')->with('status', __('Order cancelled.'));
    }

    public function refreshQuote(CryptoSellRequest $cryptoSellRequest): RedirectResponse
    {
        $this->authorizeRequest($cryptoSellRequest);

        // Immutable quotes: refresh means create a new order, not rewrite this one.
        if (! in_array($cryptoSellRequest->status, [
            CryptoSellRequest::STATUS_EXPIRED,
            CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            'pending',
        ], true)) {
            return back()->with('error', __('Cannot refresh this order.'));
        }

        if ($cryptoSellRequest->status === CryptoSellRequest::STATUS_WAITING_DEPOSIT
            || $cryptoSellRequest->status === 'pending') {
            $cryptoSellRequest->update(['status' => CryptoSellRequest::STATUS_EXPIRED]);
        }

        return redirect()
            ->route('dashboard.crypto-sell.create')
            ->with('status', __('Previous quote expired. Generate a new quote.'))
            ->withInput([
                'coin' => $cryptoSellRequest->coin,
                'network' => $cryptoSellRequest->network,
                'amount_usd' => $cryptoSellRequest->amount_usd,
            ]);
    }

    private function authorizeRequest(CryptoSellRequest $request): void
    {
        abort_unless($request->user_id === auth()->id(), 403);
    }
}
