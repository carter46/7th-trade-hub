<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Events\CryptoSold;
use App\Http\Controllers\Controller;
use App\Models\CryptoSellRequest;
use App\Models\IncomingCryptoTransaction;
use App\Models\User;
use App\Models\WalletFunding;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Admin\Services\FinancialAuditLog;
use App\Modules\Wallet\Services\CryptoExplorerUrl;
use App\Modules\Wallet\Services\NetworkRegistry;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CryptoSellController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private AuditLogService $audit,
        private FinancialAuditLog $financialAudit,
        private NetworkRegistry $networks,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $userId = $request->query('user_id');
        $status = trim((string) $request->query('status', ''));
        $coin = strtoupper(trim((string) $request->query('coin', '')));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = CryptoSellRequest::query()
            ->with(['user', 'incomingTransactions'])
            ->orderByDesc('created_at');

        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('tracking_code', 'like', '%'.$q.'%')
                    ->orWhere('tx_hash', 'like', '%'.$q.'%')
                    ->orWhere('platform_address', 'like', '%'.$q.'%');
                if (ctype_digit($q)) {
                    $builder->orWhere('id', (int) $q);
                }
                $builder->orWhereHas('user', function ($userQuery) use ($q) {
                    $userQuery->where('email', 'like', '%'.$q.'%')
                        ->orWhere('name', 'like', '%'.$q.'%')
                        ->orWhere('phone', 'like', '%'.$q.'%');
                });
            });
        }

        if ($userId !== null && $userId !== '') {
            $query->where('user_id', (int) $userId);
        }

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($coin !== '') {
            $query->whereRaw('UPPER(coin) = ?', [$coin]);
        }

        if (is_string($dateFrom) && $dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (is_string($dateTo) && $dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $requests = $query->paginate(20)->withQueryString();

        $coins = CryptoSellRequest::query()
            ->select('coin')
            ->distinct()
            ->orderBy('coin')
            ->pluck('coin')
            ->filter()
            ->values();

        $statuses = [
            CryptoSellRequest::STATUS_WAITING_DEPOSIT,
            CryptoSellRequest::STATUS_SUBMITTED,
            CryptoSellRequest::STATUS_VERIFYING,
            CryptoSellRequest::STATUS_UNDERPAID,
            CryptoSellRequest::STATUS_OVERPAID,
            CryptoSellRequest::STATUS_APPROVED,
            CryptoSellRequest::STATUS_REJECTED,
            CryptoSellRequest::STATUS_EXPIRED,
            CryptoSellRequest::STATUS_CANCELLED,
        ];

        $filterUsers = User::query()
            ->whereIn('id', CryptoSellRequest::query()->select('user_id')->distinct())
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email']);

        return view('dashboard.admin.crypto-sells.index', [
            'requests' => $requests,
            'coins' => $coins,
            'statuses' => $statuses,
            'filterUsers' => $filterUsers,
            'filters' => [
                'q' => $q,
                'user_id' => $userId,
                'status' => $status,
                'coin' => $coin,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
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

        $userId = (int) $cryptoSellRequest->user_id;
        $approvedSells = CryptoSellRequest::query()
            ->where('user_id', $userId)
            ->where('status', CryptoSellRequest::STATUS_APPROVED)
            ->count();
        $rejectedSells = CryptoSellRequest::query()
            ->where('user_id', $userId)
            ->where('status', CryptoSellRequest::STATUS_REJECTED)
            ->count();
        $totalSells = CryptoSellRequest::query()->where('user_id', $userId)->count();

        $depositWallet = $cryptoSellRequest->depositWallet;
        $openOnWallet = $depositWallet ? $depositWallet->openOrdersUsingAddress() : 0;

        $expected = (float) $cryptoSellRequest->amount_crypto;
        $received = $incoming ? (float) $incoming->amount : null;
        $difference = null;
        $differenceLabel = '—';
        if ($received !== null) {
            $difference = round($received - $expected, 10);
            $differenceLabel = abs($difference) < 1e-12
                ? 'Exact'
                : ($difference < 0 ? 'Under' : 'Over');
        }

        return view('dashboard.admin.crypto-sells.show', [
            'request' => $cryptoSellRequest,
            'incoming' => $incoming,
            'explorerUrl' => $explorerUrl,
            'confirmationsReady' => $observed >= $required,
            'required' => $required,
            'observed' => $observed,
            'networkLabel' => $this->networks->label((string) $cryptoSellRequest->network),
            'stage' => $cryptoSellRequest->trackingStage(),
            'customerTrust' => [
                'approved' => $approvedSells,
                'rejected' => $rejectedSells,
                'total' => $totalSells,
                'first' => $totalSells <= 1,
                'disputes' => $rejectedSells,
            ],
            'expectedCrypto' => $expected,
            'receivedCrypto' => $received,
            'difference' => $difference,
            'differenceLabel' => $differenceLabel,
            'depositWallet' => $depositWallet,
            'openOnWallet' => $openOnWallet,
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

        $validated = $request->validate([
            'tx_hash' => ['required', 'string', 'max:255'],
            'credit_ngn_override' => ['nullable', 'numeric', 'min:0'],
            'checklist_network' => ['accepted'],
            'checklist_destination' => ['accepted'],
            'checklist_amount' => ['accepted'],
            'checklist_confirmations' => ['accepted'],
            'checklist_valid' => ['accepted'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $txHash = trim($validated['tx_hash']);
        $duplicate = CryptoSellRequest::query()
            ->where('tx_hash', $txHash)
            ->where('id', '!=', $cryptoSellRequest->id)
            ->where('status', CryptoSellRequest::STATUS_APPROVED)
            ->exists();
        if ($duplicate) {
            return back()->with('error', __('This transaction hash was already used on an approved sell.'));
        }

        $credit = array_key_exists('credit_ngn_override', $validated) && $validated['credit_ngn_override'] !== null
            ? (float) $validated['credit_ngn_override']
            : $cryptoSellRequest->creditAmountNgn();

        try {
            DB::transaction(function () use ($cryptoSellRequest, $validated, $txHash, $credit, $request) {
                $cryptoSellRequest->tx_hash = $txHash;
                if (array_key_exists('credit_ngn_override', $validated) && $validated['credit_ngn_override'] !== null) {
                    $cryptoSellRequest->credit_ngn_override = $validated['credit_ngn_override'];
                }
                $cryptoSellRequest->admin_notes = $validated['admin_notes'] ?? $cryptoSellRequest->admin_notes;
                $cryptoSellRequest->verification_checklist = [
                    'network' => true,
                    'destination' => true,
                    'amount' => true,
                    'confirmations' => true,
                    'valid' => true,
                    'approved_by' => auth()->id(),
                    'approved_at' => now()->toIso8601String(),
                ];

                $funding = WalletFunding::create([
                    'user_id' => $cryptoSellRequest->user_id,
                    'wallet_id' => $cryptoSellRequest->wallet_id,
                    'amount' => $credit,
                    'currency' => 'NGN',
                    'method' => 'crypto',
                    'status' => 'pending',
                    'internal_status' => 'pending',
                    'reference' => $cryptoSellRequest->tracking_code ?: ('OTC-'.$cryptoSellRequest->id),
                    'provider_payment_reference' => $txHash,
                ]);

                $this->walletService->creditFromFunding(
                    $funding,
                    auth()->id(),
                    $request->ip(),
                    null,
                    'Crypto OTC sell '.$cryptoSellRequest->tracking_code
                );

                $cryptoSellRequest->wallet_funding_id = $funding->id;
                $cryptoSellRequest->status = CryptoSellRequest::STATUS_APPROVED;
                $cryptoSellRequest->save();

                event(new CryptoSold($cryptoSellRequest));

                $this->audit->log(auth()->id(), 'crypto_sell.approved', $cryptoSellRequest, null, [
                    'credit_ngn' => $credit,
                    'tracking_code' => $cryptoSellRequest->tracking_code,
                    'tx_hash' => $txHash,
                ], $request->ip());
                $this->financialAudit->log('crypto_sell.approved', [
                    'sell_id' => $cryptoSellRequest->id,
                    'tracking_code' => $cryptoSellRequest->tracking_code,
                    'credit_ngn' => $credit,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.crypto-sells.show', $cryptoSellRequest)
            ->with('status', __('Sell approved and wallet credited.'));
    }

    public function reject(CryptoSellRequest $cryptoSellRequest, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        if ($cryptoSellRequest->status === CryptoSellRequest::STATUS_APPROVED) {
            return back()->with('error', __('Approved sells cannot be rejected.'));
        }

        $before = $cryptoSellRequest->toArray();
        $cryptoSellRequest->update([
            'status' => CryptoSellRequest::STATUS_REJECTED,
            'admin_notes' => $validated['notes'],
        ]);

        $this->audit->log(auth()->id(), 'crypto_sell.rejected', $cryptoSellRequest, $before, $cryptoSellRequest->toArray(), $request->ip());

        return redirect()
            ->route('admin.crypto-sells.show', $cryptoSellRequest)
            ->with('status', __('Sell request rejected.'));
    }
}
