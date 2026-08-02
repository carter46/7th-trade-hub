<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CryptoDepositWallet;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Wallet\Services\WalletAllocationService;
use App\Support\SortOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class CryptoDepositWalletController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private WalletAllocationService $allocation,
    ) {}

    public function index(): View
    {
        $maxPerWallet = $this->allocation->maxOrdersPerWallet();
        $maxActive = $this->allocation->maxActiveWallets();

        $wallets = CryptoDepositWallet::query()->orderBy('sort_order')->orderBy('coin')->get();

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
            'maxPerWallet' => $maxPerWallet,
            'maxActive' => $maxActive,
            'capacityByPair' => $capacityByPair,
        ]);
    }

    public function treasury(): View
    {
        $wallets = CryptoDepositWallet::query()->orderBy('coin')->orderBy('network')->get();

        return view('dashboard.admin.crypto-wallets.treasury', compact('wallets'));
    }

    public function create(): View
    {
        return view('dashboard.admin.crypto-wallets.create', [
            'networksByCoin' => config('crypto.networks_by_coin', []),
            'maxActive' => $this->allocation->maxActiveWallets(),
        ]);
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
        return view('dashboard.admin.crypto-wallets.edit', [
            'wallet' => $cryptoDepositWallet,
            'openOrders' => $cryptoDepositWallet->openOrdersUsingAddress(),
            'networksByCoin' => config('crypto.networks_by_coin', []),
            'maxActive' => $this->allocation->maxActiveWallets(),
            'maxPerWallet' => $this->allocation->maxOrdersPerWallet(),
        ]);
    }

    public function update(Request $request, CryptoDepositWallet $cryptoDepositWallet): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $cryptoDepositWallet) {
                $data = $this->validated($request, $cryptoDepositWallet->id);
                $before = $cryptoDepositWallet->toArray();
                $cryptoDepositWallet->fill($data);
                if (array_key_exists('estimated_holdings', $data) && $data['estimated_holdings'] !== null) {
                    $cryptoDepositWallet->estimated_holdings_at = now();
                }
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
        $coins = array_keys(config('crypto.networks_by_coin', []));

        $validated = $request->validate([
            'coin' => ['required', 'string', 'max:20', Rule::in($coins)],
            'network' => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:255'],
            'required_confirmations' => ['required', 'integer', 'min:1', 'max:200'],
            'purpose' => ['nullable', 'string', 'max:40'],
            'owner' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'label' => ['nullable', 'string', 'max:120'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'estimated_holdings' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['coin'] = strtoupper($validated['coin']);
        $validated['network'] = $this->allocation->canonicalizeNetwork($validated['coin'], $validated['network']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_active'] && ! $this->allocation->canActivateAnother($validated['coin'], $validated['network'], $exceptWalletId)) {
            $max = $this->allocation->maxActiveWallets();
            throw new RuntimeException(
                "Maximum of {$max} active wallets reached for {$validated['coin']} / {$validated['network']}. Disable another wallet first."
            );
        }

        return $validated;
    }
}
