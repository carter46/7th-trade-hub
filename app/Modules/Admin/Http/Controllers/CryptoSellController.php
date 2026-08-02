<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Events\CryptoSold;
use App\Http\Controllers\Controller;
use App\Models\CryptoSellRequest;
use App\Models\IncomingCryptoTransaction;
use App\Models\Transaction;
use App\Models\WalletFunding;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Admin\Services\FinancialAuditLog;
use App\Modules\Wallet\Services\CryptoExplorerUrl;
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
        $requests = CryptoSellRequest::with(['user', 'incomingTransactions'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.crypto-sells.index', compact('requests'));
    }

    public function show(CryptoSellRequest $cryptoSellRequest): View
    {
        $cryptoSellRequest->load(['user', 'incomingTransactions', 'depositWallet']);
        $incoming = $cryptoSellRequest->incomingTransactions->sortByDesc('detected_at')->first()
            ?? IncomingCryptoTransaction::query()->where('tx_hash', $cryptoSellRequest->tx_hash)->first();

        $explorerUrl = CryptoExplorerUrl::forTx(
            $cryptoSellRequest->network ?? $incoming?->network,
            $cryptoSellRequest->tx_hash ?? $incoming?->tx_hash
        );

        $required = (int) ($cryptoSellRequest->required_confirmations ?? 1);
        $observed = (int) ($cryptoSellRequest->confirmations_observed ?? $incoming?->confirmations ?? 0);

        return view('dashboard.admin.crypto-sells.show', [
            'request' => $cryptoSellRequest,
            'incoming' => $incoming,
            'explorerUrl' => $explorerUrl,
            'confirmationsReady' => $observed >= $required,
            'required' => $required,
            'observed' => $observed,
        ]);
    }

    public function approve(CryptoSellRequest $cryptoSellRequest, Request $request): RedirectResponse
    {
        if ($cryptoSellRequest->status === CryptoSellRequest::STATUS_APPROVED) {
            return back()->with('status', __('Crypto sell already approved.'));
        }

        if (! $cryptoSellRequest->isApprovable()) {
            return back()->with('error', __('Request cannot be approved in its current status.'));
        }

        // Do not reprice: credit snapshotted expected_ngn (or documented override for under/overpay).
        $validated = $request->validate([
            'tx_hash' => ['nullable', 'string', 'max:255'],
            'credit_ngn_override' => ['nullable', 'numeric', 'min:0'],
            'checklist_network' => ['accepted'],
            'checklist_destination' => ['accepted'],
            'checklist_amount' => ['accepted'],
            'checklist_confirmations' => ['accepted'],
            'checklist_valid' => ['accepted'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $txHash = $validated['tx_hash'] ?? $cryptoSellRequest->tx_hash;
        if (! filled($txHash)) {
            return back()->with('error', __('Transaction hash is required.'));
        }

        $duplicate = CryptoSellRequest::query()
            ->where('tx_hash', $txHash)
            ->where('id', '!=', $cryptoSellRequest->id)
            ->exists();
        if ($duplicate) {
            return back()->with('error', __('This transaction hash is already used on another order.'));
        }

        $walletBefore = $cryptoSellRequest->wallet?->replicate();
        $creditAmount = isset($validated['credit_ngn_override']) && $validated['credit_ngn_override'] !== null
            ? (float) $validated['credit_ngn_override']
            : $cryptoSellRequest->creditAmountNgn();

        DB::transaction(function () use ($cryptoSellRequest, $request, $validated, $txHash, $creditAmount) {
            $cryptoSellRequest = CryptoSellRequest::where('id', $cryptoSellRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($cryptoSellRequest->status === CryptoSellRequest::STATUS_APPROVED) {
                return;
            }

            $checklist = [
                'network' => true,
                'destination' => true,
                'amount' => true,
                'confirmations' => true,
                'valid' => true,
                'approved_by' => auth()->id(),
                'approved_at' => now()->toIso8601String(),
            ];

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
                    $cryptoSellRequest->update([
                        'status' => CryptoSellRequest::STATUS_APPROVED,
                        'tx_hash' => $txHash,
                        'verification_checklist' => $checklist,
                        'admin_notes' => $validated['admin_notes'] ?? $cryptoSellRequest->admin_notes,
                        'credit_ngn_override' => $validated['credit_ngn_override'] ?? $cryptoSellRequest->credit_ngn_override,
                    ]);

                    return;
                }
            }

            $funding = WalletFunding::create([
                'user_id' => $cryptoSellRequest->user_id,
                'wallet_id' => $cryptoSellRequest->wallet_id,
                'method' => 'crypto',
                'amount' => $creditAmount,
                'currency' => 'NGN',
                'status' => 'pending',
                'reference' => 'DEP-'.strtoupper(Str::random(10)),
                'metadata' => [
                    'coin' => $cryptoSellRequest->coin,
                    'network' => $cryptoSellRequest->network,
                    'amount_crypto' => $cryptoSellRequest->amount_crypto,
                    'amount_usd' => $cryptoSellRequest->amount_usd,
                    'rate_ngn' => $cryptoSellRequest->quoted_rate_ngn,
                    'market_rate_ngn' => $cryptoSellRequest->market_rate_ngn,
                    'spread_ngn' => $cryptoSellRequest->spread_ngn,
                    'tx_hash' => $txHash,
                    'crypto_sell_request_id' => $cryptoSellRequest->id,
                    'immutable_quote' => true,
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
                'status' => CryptoSellRequest::STATUS_APPROVED,
                'tx_hash' => $txHash,
                'wallet_funding_id' => $funding->id,
                'verification_checklist' => $checklist,
                'admin_notes' => $validated['admin_notes'] ?? $cryptoSellRequest->admin_notes,
                'credit_ngn_override' => $validated['credit_ngn_override'] ?? $cryptoSellRequest->credit_ngn_override,
            ]);

            IncomingCryptoTransaction::query()
                ->where('tx_hash', $txHash)
                ->update(['status' => IncomingCryptoTransaction::STATUS_APPROVED, 'matched_order_id' => $cryptoSellRequest->id]);
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
                    (float) $cryptoSellRequest->creditAmountNgn(),
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

        return redirect()
            ->route('admin.crypto-sells.show', $cryptoSellRequest)
            ->with('status', __('Crypto sell approved. Wallet credited ₦:amount (quoted amount).', [
                'amount' => number_format($cryptoSellRequest->creditAmountNgn(), 2),
            ]));
    }

    public function reject(CryptoSellRequest $cryptoSellRequest, Request $request): RedirectResponse
    {
        if (! $cryptoSellRequest->isApprovable() && $cryptoSellRequest->status !== 'pending') {
            return back()->with('error', __('Request cannot be rejected in its current status.'));
        }

        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        $cryptoSellRequest->update([
            'status' => CryptoSellRequest::STATUS_REJECTED,
            'admin_notes' => $request->input('notes'),
        ]);

        $this->audit->log(auth()->id(), 'crypto_sell.rejected', $cryptoSellRequest, null, $cryptoSellRequest->toArray(), $request->ip());

        return back()->with('status', __('Crypto sell request rejected.'));
    }
}
