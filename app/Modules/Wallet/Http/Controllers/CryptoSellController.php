<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CryptoDepositWallet;
use App\Models\CryptoSellRequest;
use App\Models\ExchangeRate;
use App\Modules\Wallet\Services\CryptoExplorerUrl;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use App\Modules\Wallet\Services\NetworkRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class CryptoSellController extends Controller
{
    public function __construct(
        private ExchangeQuoteService $quoteService,
        private \App\Modules\Wallet\Services\WalletAllocationService $allocation,
        private NetworkRegistry $networks,
    ) {}

    public function index(): View
    {
        $userId = auth()->id();
        $openOrder = CryptoSellRequest::query()
            ->where('user_id', $userId)
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->orderByDesc('id')
            ->first();

        $requests = CryptoSellRequest::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.deposit.crypto-index', [
            'requests' => $requests,
            'wallet' => auth()->user()->wallet,
            'openOrder' => $openOrder,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfOpenOrder()) {
            return $redirect;
        }

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

        $walletCoins = $wallets->keys();
        if ($walletCoins->isNotEmpty()) {
            $coins = $coins->filter(fn ($c) => $walletCoins->contains($c))->values();
            if ($coins->isEmpty()) {
                $coins = $walletCoins->values();
            }
        }

        $rateMap = [];
        foreach ($coins as $symbol) {
            if (! $this->networks->canEnableForOtc($symbol)) {
                continue;
            }

            $rate = $catalog->first(fn ($r) => strtoupper((string) $r->asset) === $symbol);
            $customer = $this->quoteService->resolveCustomerRateForCoin($symbol);
            $coinWallets = $wallets[$symbol] ?? collect();
            $allowed = collect($this->networks->monitorableIdsForCoin($symbol));
            $networks = $coinWallets
                ->groupBy(fn ($w) => $this->networks->resolveId((string) $w->network))
                ->filter(fn ($group, $networkId) => $allowed->contains($networkId))
                ->map(function ($group, $networkId) {
                    return [
                        'network' => $networkId,
                        'label' => $this->networks->label((string) $networkId),
                        'id' => $group->first()->id,
                        'confirmations' => (int) $group->min('required_confirmations'),
                        'available' => $group->where('is_active', true)->count(),
                    ];
                })
                ->values()
                ->all();

            if ($networks === []) {
                continue;
            }

            try {
                $coinUsd = $this->quoteService->coinUsdPrice($symbol);
            } catch (\Throwable) {
                $coinUsd = 0;
            }

            $preferred = $this->networks->preferredNetworkId($symbol);
            $networkIds = array_column($networks, 'network');
            if ($preferred && ! in_array($preferred, $networkIds, true)) {
                $preferred = $networkIds[0] ?? null;
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
                'preferred_network' => $preferred ?? ($networkIds[0] ?? null),
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
        if ($redirect = $this->redirectIfOpenOrder()) {
            return $redirect;
        }

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
        $cryptoSellRequest->expireIfNeeded();
        $cryptoSellRequest->refresh();
        $cryptoSellRequest->load(['incomingTransactions']);

        $supportUrl = Route::has('dashboard.support.index')
            ? route('dashboard.support.index')
            : url('/dashboard/support');

        return view('dashboard.user.deposit.crypto-show', [
            'sell' => $cryptoSellRequest,
            'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.urlencode((string) $cryptoSellRequest->platform_address),
            'statusUrl' => route('dashboard.crypto-sell.status', $cryptoSellRequest),
            'supportUrl' => $supportUrl,
            'initialPayload' => $this->statusPayload($cryptoSellRequest),
        ]);
    }

    public function status(CryptoSellRequest $cryptoSellRequest): JsonResponse
    {
        $this->authorizeRequest($cryptoSellRequest);
        $cryptoSellRequest->expireIfNeeded();
        $cryptoSellRequest->refresh();
        $cryptoSellRequest->load(['incomingTransactions', 'wallet']);

        return response()->json($this->statusPayload($cryptoSellRequest));
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

    /**
     * @return array<string, mixed>
     */
    private function statusPayload(CryptoSellRequest $sell): array
    {
        $incoming = $sell->incomingTransactions->sortByDesc('detected_at')->first();
        $stage = $sell->trackingStage();
        $required = max(1, (int) ($sell->required_confirmations ?? 1));
        $observed = (int) ($sell->confirmations_observed ?? $incoming?->confirmations ?? 0);
        $amountReceived = $incoming ? (float) $incoming->amount : null;
        $expectedCrypto = (float) $sell->amount_crypto;
        $shortfall = ($amountReceived !== null && $amountReceived < $expectedCrypto)
            ? round($expectedCrypto - $amountReceived, 10)
            : null;

        $txHash = $sell->tx_hash ?: ($incoming?->tx_hash);
        $secondsRemaining = 0;
        if ($sell->expires_at && $stage === 'waiting_deposit') {
            $secondsRemaining = max(0, $sell->expires_at->getTimestamp() - now()->getTimestamp());
        }

        $walletBalance = (float) (auth()->user()?->wallet?->balance ?? $sell->wallet?->balance ?? 0);

        return [
            'tracking_code' => $sell->tracking_code,
            'status' => $sell->status,
            'stage' => $stage,
            'coin' => $sell->coin,
            'network' => $this->networks->label((string) $sell->network),
            'network_id' => $this->networks->resolveId((string) $sell->network),
            'platform_address' => $sell->platform_address,
            'amount_usd' => (float) $sell->amount_usd,
            'amount_crypto' => $expectedCrypto,
            'amount_received' => $amountReceived,
            'shortfall' => $shortfall,
            'expected_ngn' => (float) $sell->expected_ngn,
            'credit_ngn' => $sell->creditAmountNgn(),
            'quoted_rate_ngn' => (float) $sell->quoted_rate_ngn,
            'wallet_available_ngn' => $walletBalance,
            'expires_at' => optional($sell->expires_at)?->toIso8601String(),
            'seconds_remaining' => (int) $secondsRemaining,
            'confirmations_observed' => $observed,
            'required_confirmations' => $required,
            'conf_progress' => min(1, $observed / $required),
            'tx_hash' => $txHash,
            'detected_at' => optional($incoming?->detected_at ?? $incoming?->created_at)?->toIso8601String(),
            'explorer_url' => CryptoExplorerUrl::forTx($sell->network, $txHash),
            'admin_notes' => $sell->admin_notes,
            'amount_match_status' => $sell->amount_match_status,
            'poll_interval_ms' => $sell->pollIntervalMs(),
            'show_countdown' => $stage === 'waiting_deposit',
            'show_confirmation_panel' => in_array($stage, ['deposit_detected', 'awaiting_admin', 'underpaid', 'overpaid'], true),
            'open' => $sell->isOpen() && $stage !== 'expired',
            'is_terminal' => $sell->isTerminal() || $stage === 'expired',
            'is_expired' => $stage === 'expired',
        ];
    }

    private function redirectIfOpenOrder(): ?RedirectResponse
    {
        $open = CryptoSellRequest::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', CryptoSellRequest::OPEN_STATUSES)
            ->orderByDesc('id')
            ->first();

        if (! $open) {
            return null;
        }

        $open->expireIfNeeded();
        $open->refresh();
        if (! $open->isOpen()) {
            return null;
        }

        return redirect()
            ->route('dashboard.crypto-sell.show', $open)
            ->with('status', __('You have an active sell order. Resume tracking below.'));
    }

    private function authorizeRequest(CryptoSellRequest $request): void
    {
        abort_unless($request->user_id === auth()->id(), 403);
    }
}
