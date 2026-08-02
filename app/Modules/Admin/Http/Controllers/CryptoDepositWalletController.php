<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CryptoDepositWallet;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use App\Modules\Wallet\Services\WalletAllocationService;
use App\Support\SortOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class CryptoDepositWalletController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private WalletAllocationService $allocation,
        private ExchangeQuoteService $quotes,
    ) {}

    public function index(): View
    {
        $maxPerWallet = $this->allocation->maxOrdersPerWallet();
        $maxActive = $this->allocation->maxActiveWallets();

        $wallets = CryptoDepositWallet::query()->orderBy('sort_order')->orderBy('coin')->get();

        $logos = \App\Models\ExchangeRate::query()
            ->get(['asset', 'logo_url', 'coingecko_id'])
            ->mapWithKeys(fn ($r) => [strtoupper((string) $r->asset) => $r->resolvedLogoUrl()]);

        $capacityByPair = [];
        foreach ($wallets->where('is_active', true)->groupBy(fn ($w) => strtoupper($w->coin).'|'.$w->network) as $pair => $group) {
            [$coin, $network] = explode('|', $pair, 2);
            $capacityByPair[$pair] = [
                'label' => "{$coin} / {$network} ({$group->count()}/{$maxActive})",
                'count' => $group->count(),
            ];
        }

        return view('dashboard.admin.crypto-wallets.index', [
            'wallets' => $wallets,
            'logos' => $logos,
            'maxPerWallet' => $maxPerWallet,
            'maxActive' => $maxActive,
            'capacityByPair' => $capacityByPair,
        ]);
    }

    public function treasury(): View
    {
        $wallets = CryptoDepositWallet::query()
            ->with(['balanceHistory' => fn ($q) => $q->orderByDesc('recorded_at')->limit(8)])
            ->orderBy('coin')
            ->orderBy('network')
            ->get();

        $coinCards = [];
        $portfolioUsd = 0.0;
        $portfolioNgn = 0.0;

        foreach ($wallets->groupBy(fn ($w) => strtoupper((string) $w->coin)) as $coin => $group) {
            $usdPrice = $this->safeCoinUsd($coin);
            $ngnPerUsd = (float) ($this->quotes->resolveCustomerRateForCoin($coin)['rate'] ?? 0);
            $totalBalance = (float) $group->sum(fn ($w) => (float) ($w->live_balance ?? 0));
            $usdValue = $totalBalance * $usdPrice;
            $ngnValue = $usdValue * $ngnPerUsd;
            $portfolioUsd += $usdValue;
            $portfolioNgn += $ngnValue;

            $rows = [];
            foreach ($group as $wallet) {
                $current = (float) ($wallet->live_balance ?? 0);
                $reserved = $wallet->reservedCrypto();
                $rows[] = [
                    'wallet' => $wallet,
                    'current' => $current,
                    'reserved' => $reserved,
                    'available' => max(0, $current - $reserved),
                    'open_orders' => $wallet->openOrdersUsingAddress(),
                    'history' => $wallet->balanceHistory,
                ];
            }

            $coinCards[$coin] = [
                'coin' => $coin,
                'wallet_count' => $group->count(),
                'total_balance' => $totalBalance,
                'usd' => $usdValue,
                'ngn' => $ngnValue,
                'usd_price' => $usdPrice,
                'ngn_per_usd' => $ngnPerUsd,
                'allocation_pct' => 0.0,
                'rows' => $rows,
            ];
        }

        foreach ($coinCards as $coin => $card) {
            $coinCards[$coin]['allocation_pct'] = $portfolioUsd > 0
                ? round(($card['usd'] / $portfolioUsd) * 100, 1)
                : 0.0;
        }

        return view('dashboard.admin.crypto-wallets.treasury', [
            'wallets' => $wallets,
            'coinCards' => $coinCards,
            'portfolioUsd' => $portfolioUsd,
            'portfolioNgn' => $portfolioNgn,
        ]);
    }

    public function refreshTreasury(Request $request): RedirectResponse
    {
        Artisan::call('crypto:poll-balances');
        $output = trim(Artisan::output());
        $this->audit->log(auth()->id(), 'crypto_wallet.treasury_refresh', null, null, ['output' => $output], $request->ip());

        return redirect()
            ->route('admin.crypto-wallets.treasury')
            ->with('status', $output !== '' ? $output : __('Balances refreshed.'));
    }

    public function create(): View
    {
        return view('dashboard.admin.crypto-wallets.create', $this->walletFormData());
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $wallet = DB::transaction(function () use ($request) {
                $data = $this->validated($request);
                $data['sort_order'] = SortOrder::next(CryptoDepositWallet::class);
                $wallet = CryptoDepositWallet::query()->create($data);
                $this->audit->log(auth()->id(), 'crypto_wallet.created', $wallet, null, $wallet->toArray(), $request->ip());

                return $wallet;
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.crypto-wallets')->with('status', __('Deposit wallet created.'));
    }

    public function edit(CryptoDepositWallet $cryptoDepositWallet): View
    {
        return view('dashboard.admin.crypto-wallets.edit', array_merge($this->walletFormData($cryptoDepositWallet), [
            'wallet' => $cryptoDepositWallet,
            'openOrders' => $cryptoDepositWallet->openOrdersUsingAddress(),
            'maxPerWallet' => $this->allocation->maxOrdersPerWallet(),
        ]));
    }

    public function update(Request $request, CryptoDepositWallet $cryptoDepositWallet): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $cryptoDepositWallet) {
                $data = $this->validated($request, $cryptoDepositWallet->id);
                $before = $cryptoDepositWallet->toArray();
                $cryptoDepositWallet->fill($data);
                $cryptoDepositWallet->save();
                $this->audit->log(auth()->id(), 'crypto_wallet.updated', $cryptoDepositWallet, $before, $cryptoDepositWallet->toArray(), $request->ip());
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.crypto-wallets')->with('status', __('Deposit wallet updated.'));
    }

    public function destroy(Request $request, CryptoDepositWallet $cryptoDepositWallet): RedirectResponse
    {
        $open = $cryptoDepositWallet->openOrdersUsingAddress();
        if ($open > 0) {
            return back()->with('error', __(':n open order(s) still use this wallet address. Disable it instead of deleting.', ['n' => $open]));
        }

        $snapshot = $cryptoDepositWallet->toArray();
        $cryptoDepositWallet->delete();
        $this->audit->log(auth()->id(), 'crypto_wallet.deleted', null, $snapshot, null, $request->ip());

        return redirect()->route('admin.crypto-wallets')->with('status', __('Deposit wallet deleted.'));
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?int $exceptWalletId = null): array
    {
        $existing = $exceptWalletId
            ? CryptoDepositWallet::query()->find($exceptWalletId)
            : null;
        $form = $this->walletFormData($existing);
        /** @var array<string, list<string>> $networksByCoin */
        $networksByCoin = $form['networksByCoin'];
        $coins = array_keys($networksByCoin);

        if ($coins === []) {
            throw new RuntimeException('Add an active coin in Coin Catalog before creating a deposit wallet.');
        }

        $validated = $request->validate([
            'coin' => ['required', 'string', 'max:20', Rule::in($coins)],
            'network' => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_exchange_managed' => ['nullable', 'boolean'],
        ]);

        $validated['coin'] = strtoupper($validated['coin']);
        $validated['network'] = $this->allocation->canonicalizeNetwork($validated['coin'], $validated['network']);
        $allowed = $networksByCoin[$validated['coin']] ?? [];
        if (! in_array($validated['network'], $allowed, true)) {
            throw new RuntimeException("Network {$validated['network']} is not allowed for {$validated['coin']}.");
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_exchange_managed'] = $request->boolean('is_exchange_managed');
        $validated['required_confirmations'] = $this->defaultConfirmations($validated['network']);

        if ($validated['is_active'] && ! $this->allocation->canActivateAnother($validated['coin'], $validated['network'], $exceptWalletId)) {
            $max = $this->allocation->maxActiveWallets();
            throw new RuntimeException(
                "Maximum of {$max} active wallets reached for {$validated['coin']} / {$validated['network']}. Disable another wallet first."
            );
        }

        return $validated;
    }

    /**
     * @return array{
     *   catalogCoins: list<array{symbol: string, logo: ?string}>,
     *   networksByCoin: array<string, list<string>>,
     *   maxActive: int
     * }
     */
    private function walletFormData(?CryptoDepositWallet $wallet = null): array
    {
        $allNetworks = config('crypto.networks_by_coin', []);
        $catalog = \App\Models\ExchangeRate::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('asset')
            ->get();

        $catalogCoins = [];
        $networksByCoin = [];
        foreach ($catalog as $rate) {
            $symbol = strtoupper((string) $rate->asset);
            if ($symbol === '' || ! isset($allNetworks[$symbol])) {
                continue;
            }
            $catalogCoins[] = [
                'symbol' => $symbol,
                'logo' => $rate->resolvedLogoUrl(),
            ];
            $networksByCoin[$symbol] = $allNetworks[$symbol];
        }

        // Keep the current wallet coin selectable even if it was deactivated in catalog.
        if ($wallet) {
            $symbol = strtoupper((string) $wallet->coin);
            if ($symbol !== '' && isset($allNetworks[$symbol]) && ! isset($networksByCoin[$symbol])) {
                $catalogCoins[] = [
                    'symbol' => $symbol,
                    'logo' => \App\Models\ExchangeRate::query()
                        ->whereRaw('UPPER(asset) = ?', [$symbol])
                        ->first()
                        ?->resolvedLogoUrl(),
                ];
                $networksByCoin[$symbol] = $allNetworks[$symbol];
            }
        }

        return [
            'catalogCoins' => $catalogCoins,
            'networksByCoin' => $networksByCoin,
            'maxActive' => $this->allocation->maxActiveWallets(),
        ];
    }

    private function defaultConfirmations(string $network): int
    {
        $map = config('crypto.default_confirmations', []);
        foreach ($map as $label => $count) {
            if (strcasecmp((string) $label, $network) === 0) {
                return max(1, (int) $count);
            }
        }

        return 3;
    }

    private function safeCoinUsd(string $coin): float
    {
        try {
            return max(0, $this->quotes->coinUsdPrice($coin));
        } catch (\Throwable) {
            return 0.0;
        }
    }
}
