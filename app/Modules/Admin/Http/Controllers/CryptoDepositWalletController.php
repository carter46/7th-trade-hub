<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CryptoDepositWallet;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Wallet\Services\ExchangeQuoteService;
use App\Modules\Wallet\Services\NetworkRegistry;
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
        private NetworkRegistry $networks,
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
        foreach ($wallets->where('is_active', true)->groupBy(
            fn ($w) => strtoupper($w->coin).'|'.$this->networks->resolveId((string) $w->network)
        ) as $pair => $group) {
            [$coin, $networkId] = explode('|', $pair, 2);
            $networkLabel = $this->networks->label($networkId);
            $capacityByPair[$pair] = [
                'label' => "{$coin} / {$networkLabel} ({$group->count()}/{$maxActive})",
                'count' => $group->count(),
            ];
        }

        $supportsByNetwork = [];
        foreach ($this->networks->ids() as $networkId) {
            $supportsByNetwork[$networkId] = $this->networks->coinsUsingNetwork($networkId);
        }

        $valuations = [];
        foreach ($wallets->pluck('coin')->unique() as $coin) {
            $symbol = strtoupper((string) $coin);
            $usdPrice = $this->safeCoinUsd($symbol);
            $ngnPerUsd = (float) ($this->quotes->resolveCustomerRateForCoin($symbol)['rate'] ?? 0);
            $valuations[$symbol] = [
                'usd_price' => $usdPrice,
                'ngn_per_usd' => $ngnPerUsd,
            ];
        }

        return view('dashboard.admin.crypto-wallets.index', [
            'wallets' => $wallets,
            'logos' => $logos,
            'maxPerWallet' => $maxPerWallet,
            'maxActive' => $maxActive,
            'capacityByPair' => $capacityByPair,
            'supportsByNetwork' => $supportsByNetwork,
            'networkRegistry' => $this->networks,
            'valuations' => $valuations,
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
        $coin = strtoupper((string) $cryptoDepositWallet->coin);
        $balance = (float) ($cryptoDepositWallet->live_balance ?? 0);
        $usdPrice = $this->safeCoinUsd($coin);
        $ngnPerUsd = (float) ($this->quotes->resolveCustomerRateForCoin($coin)['rate'] ?? 0);
        $networkId = $this->networks->resolveId((string) $cryptoDepositWallet->network);

        return view('dashboard.admin.crypto-wallets.edit', array_merge($this->walletFormData($cryptoDepositWallet), [
            'wallet' => $cryptoDepositWallet,
            'openOrders' => $cryptoDepositWallet->openOrdersUsingAddress(),
            'maxPerWallet' => $this->allocation->maxOrdersPerWallet(),
            'supportsCoins' => $this->networks->coinsUsingNetwork($networkId),
            'networkLabel' => $this->networks->label($networkId),
            'liveBalance' => $balance,
            'liveBalanceUsd' => $balance * $usdPrice,
            'liveBalanceNgn' => $balance * $usdPrice * $ngnPerUsd,
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
        /** @var array<string, list<array{id: string, label: string}>> $networksByCoin */
        $networksByCoin = $form['networksByCoin'];
        $coins = array_keys($networksByCoin);

        if ($coins === []) {
            throw new RuntimeException('Add an active coin with a monitorable deposit network in Coin Catalog before creating a wallet.');
        }

        $validated = $request->validate([
            'coin' => ['required', 'string', 'max:20', Rule::in($coins)],
            'network' => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:255'],
            'required_confirmations' => ['nullable', 'integer', 'min:1', 'max:500'],
            'label' => ['nullable', 'string', 'max:120'],
            'purpose' => ['nullable', 'string', 'max:120'],
            'owner' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
            'is_exchange_managed' => ['nullable', 'boolean'],
        ]);

        $validated['coin'] = strtoupper($validated['coin']);
        if (! $this->networks->canEnableForOtc($validated['coin'])) {
            $supported = collect($this->networks->checkboxOptions())
                ->where('monitorable', true)
                ->pluck('label')
                ->implode(', ');
            throw new RuntimeException(
                "{$validated['coin']} is unsupported for OTC deposits. No blockchain monitor is configured for its networks. Currently supported: {$supported}."
            );
        }

        $validated['network'] = $this->allocation->canonicalizeNetwork($validated['coin'], $validated['network']);
        $allowedIds = array_column($networksByCoin[$validated['coin']] ?? [], 'id');
        if (! in_array($validated['network'], $allowedIds, true)) {
            throw new RuntimeException('Selected network is not allowed for '.$validated['coin'].'.');
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_exchange_managed'] = $request->boolean('is_exchange_managed');
        $validated['required_confirmations'] = isset($validated['required_confirmations'])
            ? max(1, (int) $validated['required_confirmations'])
            : $this->networks->defaultConfirmations($validated['network']);

        if ($validated['is_active'] && ! $this->allocation->canActivateAnother($validated['coin'], $validated['network'], $exceptWalletId)) {
            $max = $this->allocation->maxActiveWallets();
            $label = $this->networks->label($validated['network']);
            throw new RuntimeException(
                "Maximum of {$max} active wallets reached for {$validated['coin']} / {$label}. Disable another wallet first."
            );
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function walletFormData(?CryptoDepositWallet $wallet = null): array
    {
        $catalog = \App\Models\ExchangeRate::query()
            ->orderBy('sort_order')
            ->orderBy('asset')
            ->get();

        $catalogCoins = [];
        $networksByCoin = [];
        $unsupportedCoins = [];
        $defaultConfs = [];
        $usedBy = [];

        foreach ($this->networks->ids() as $networkId) {
            $coins = $this->networks->coinsUsingNetwork($networkId);
            $usedBy[$networkId] = [
                'count' => count($coins),
                'coins' => $coins,
                'label' => $this->networks->label($networkId),
            ];
            $defaultConfs[$networkId] = $this->networks->defaultConfirmations($networkId);
        }

        foreach ($catalog as $rate) {
            $symbol = strtoupper((string) $rate->asset);
            if ($symbol === '') {
                continue;
            }
            $options = $this->networks->optionsForCoin($symbol);
            if ($options === []) {
                if ($rate->is_active) {
                    $unsupportedCoins[] = [
                        'symbol' => $symbol,
                        'reason' => 'No monitorable deposit network in Coin Catalog.',
                    ];
                }

                continue;
            }
            if (! $rate->is_active && (! $wallet || strtoupper((string) $wallet->coin) !== $symbol)) {
                continue;
            }
            $catalogCoins[] = [
                'symbol' => $symbol,
                'logo' => $rate->resolvedLogoUrl(),
            ];
            $networksByCoin[$symbol] = $options;
        }

        if ($wallet) {
            $symbol = strtoupper((string) $wallet->coin);
            if ($symbol !== '' && ! isset($networksByCoin[$symbol])) {
                $options = $this->networks->optionsForCoin($symbol);
                if ($options === []) {
                    $options = [[
                        'id' => $this->networks->resolveId((string) $wallet->network),
                        'label' => $this->networks->label((string) $wallet->network),
                    ]];
                }
                $catalogCoins[] = [
                    'symbol' => $symbol,
                    'logo' => \App\Models\ExchangeRate::query()
                        ->whereRaw('UPPER(asset) = ?', [$symbol])
                        ->first()
                        ?->resolvedLogoUrl(),
                ];
                $networksByCoin[$symbol] = $options;
            }
        }

        return [
            'catalogCoins' => $catalogCoins,
            'networksByCoin' => $networksByCoin,
            'unsupportedCoins' => $unsupportedCoins,
            'usedByByNetwork' => $usedBy,
            'defaultConfirmationsByNetwork' => $defaultConfs,
            'supportedNetworkLabels' => collect($this->networks->checkboxOptions())
                ->where('monitorable', true)
                ->pluck('label')
                ->values()
                ->all(),
            'maxActive' => $this->allocation->maxActiveWallets(),
        ];
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
