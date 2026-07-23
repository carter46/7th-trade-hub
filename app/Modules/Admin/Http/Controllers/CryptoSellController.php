<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Events\CryptoSold;
use App\Http\Controllers\Controller;
use App\Models\CryptoSellRequest;
use App\Models\Transaction;
use App\Models\WalletFunding;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Admin\Services\FinancialAuditLog;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CryptoSellController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private AuditLogService $audit,
        private FinancialAuditLog $financialAudit,
    ) {}

    public function index(): View
    {
        $requests = CryptoSellRequest::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.crypto-sells', compact('requests'));
    }

    public function approve(CryptoSellRequest $cryptoSellRequest, Request $request): RedirectResponse
    {
        if ($cryptoSellRequest->status === 'approved') {
            return back()->with('status', __('Crypto sell already approved.'));
        }

        if ($cryptoSellRequest->status !== 'pending') {
            return back()->with('error', __('Request is not pending.'));
        }

        if ($cryptoSellRequest->isQuoteExpired()) {
            return back()->with('error', __('Quote expired. User must request a new quote.'));
        }

        $request->validate(['tx_hash' => ['required', 'string', 'max:255']]);

        $walletBefore = $cryptoSellRequest->wallet?->replicate();

        DB::transaction(function () use ($cryptoSellRequest, $request) {
            $cryptoSellRequest = CryptoSellRequest::where('id', $cryptoSellRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($cryptoSellRequest->status === 'approved') {
                return;
            }

            if ($cryptoSellRequest->wallet_funding_id) {
                $funding = WalletFunding::find($cryptoSellRequest->wallet_funding_id);
                if ($funding) {
                    $this->walletService->creditFromFunding(
                        $funding,
                        auth()->id(),
                        $request->ip(),
                        substr((string) $request->userAgent(), 0, 255),
                        $request->input('reason', 'Crypto OTC verified'),
                    );

                    return;
                }
            }

            $funding = WalletFunding::create([
                'user_id' => $cryptoSellRequest->user_id,
                'wallet_id' => $cryptoSellRequest->wallet_id,
                'method' => 'crypto',
                'amount' => $cryptoSellRequest->expected_ngn,
                'currency' => 'NGN',
                'status' => 'pending',
                'reference' => 'DEP-'.strtoupper(Str::random(10)),
                'metadata' => [
                    'coin' => $cryptoSellRequest->coin,
                    'network' => $cryptoSellRequest->network,
                    'amount_crypto' => $cryptoSellRequest->amount_crypto,
                    'rate_ngn' => $cryptoSellRequest->quoted_rate_ngn,
                    'tx_hash' => $request->tx_hash,
                    'crypto_sell_request_id' => $cryptoSellRequest->id,
                ],
            ]);

            $this->walletService->creditFromFunding(
                $funding,
                auth()->id(),
                $request->ip(),
                substr((string) $request->userAgent(), 0, 255),
                $request->input('reason', 'Crypto OTC verified'),
            );

            $cryptoSellRequest->update([
                'status' => 'approved',
                'tx_hash' => $request->tx_hash,
                'wallet_funding_id' => $funding->id,
            ]);
        });

        $cryptoSellRequest->refresh();
        $walletAfter = $cryptoSellRequest->wallet;

        $fundingId = $cryptoSellRequest->wallet_funding_id;
        if ($fundingId) {
            $txn = Transaction::query()->where('wallet_funding_id', $fundingId)->latest('id')->first();
            if ($txn) {
                CryptoSold::dispatch(
                    (int) $cryptoSellRequest->user_id,
                    (int) $txn->id,
                    (float) $cryptoSellRequest->expected_ngn,
                    'NGN'
                );
            }
        }

        $this->financialAudit->logMoneyAction(
            auth()->id(),
            'crypto_sell.approved',
            $cryptoSellRequest,
            $walletBefore,
            $walletAfter,
            $request->ip(),
            $request->userAgent(),
            $request->header('X-Request-Id'),
        );

        return back()->with('status', __('Crypto sell approved. Wallet credited.'));
    }

    public function reject(CryptoSellRequest $cryptoSellRequest, Request $request): RedirectResponse
    {
        if ($cryptoSellRequest->status !== 'pending') {
            return back()->with('error', __('Request is not pending.'));
        }

        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        $cryptoSellRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('notes'),
        ]);

        $this->audit->log(auth()->id(), 'crypto_sell.rejected', $cryptoSellRequest, null, $cryptoSellRequest->toArray(), $request->ip());

        return back()->with('status', __('Crypto sell request rejected.'));
    }
}
