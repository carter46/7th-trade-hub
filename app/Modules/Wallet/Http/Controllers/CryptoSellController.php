<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CryptoSellRequest;
use App\Models\WalletFunding;
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
        return view('dashboard.user.deposit.crypto-create', [
            'wallet' => auth()->user()->wallet,
            'coins' => ['BTC', 'ETH', 'USDT'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            return redirect()->route('dashboard.wallet')->with('error', __('Create a wallet first.'));
        }

        $validated = $request->validate([
            'coin' => ['required', 'string', Rule::in(['BTC', 'ETH', 'USDT'])],
            'network' => ['nullable', 'string', 'max:20'],
            'amount_crypto' => ['required', 'numeric', 'min:0.00000001'],
        ]);

        $quote = $this->priceService->quoteNgn($validated['coin'], (float) $validated['amount_crypto']);

        CryptoSellRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'coin' => strtoupper($validated['coin']),
            'network' => $validated['network'],
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
