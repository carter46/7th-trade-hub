<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Modules\Admin\Services\FinancialAuditLog;
use App\Modules\Wallet\Payments\Contracts\PaymentRailInterface;
use App\Modules\Wallet\Services\WalletService;
use App\Modules\Wallet\Services\WithdrawalPayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class WithdrawalAdminController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private WithdrawalPayoutService $payouts,
        private PaymentRailInterface $rail,
        private FinancialAuditLog $financialAudit,
    ) {}

    public function index(): View
    {
        $withdrawals = Withdrawal::with(['user', 'approver'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $merchantBalance = null;
        if ($this->rail->isConfigured()) {
            try {
                $merchantBalance = $this->payouts->merchantBalance();
            } catch (\Throwable) {
                $merchantBalance = null;
            }
        }

        return view('dashboard.admin.withdrawals', [
            'withdrawals' => $withdrawals,
            'merchantBalance' => $merchantBalance,
            'monnifyEnabled' => $this->rail->isConfigured(),
        ]);
    }

    public function approve(Withdrawal $withdrawal, Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'approval_note' => ['nullable', 'string', 'max:500'],
                'confirm_send' => ['required', 'accepted'],
            ]);
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first() ?: __('Confirmation is required.'));
        }

        $walletBefore = $withdrawal->wallet?->replicate();

        try {
            $this->payouts->approveAndSend(
                $withdrawal,
                (int) auth()->id(),
                $request->ip(),
                $validated['approval_note'] ?? null,
            );
        } catch (\Throwable $e) {
            report($e);
            $withdrawal->refresh();
            if ($withdrawal->status === 'completed' || $withdrawal->internal_status === 'completed') {
                return back()->with('status', __('Withdrawal already completed.'));
            }

            return back()->with('error', $e->getMessage());
        }

        $withdrawal->refresh();
        $walletAfter = $withdrawal->wallet;

        $this->financialAudit->logMoneyAction(
            auth()->id(),
            'withdrawal.approved_and_sent',
            $withdrawal,
            $walletBefore,
            $walletAfter,
            $request->ip(),
            $request->userAgent(),
            $request->header('X-Request-Id'),
            FinancialAuditLog::sanitizeWithdrawal($withdrawal->only([
                'id', 'reference', 'amount', 'status', 'internal_status', 'user_id', 'provider_payout_reference',
            ])),
        );

        $msg = $withdrawal->internal_status === 'completed'
            ? __('Withdrawal approved and completed.')
            : __('Withdrawal approved and sent to Monnify. Waiting for payout confirmation.');

        return back()->with('status', $msg);
    }

    public function reject(Withdrawal $withdrawal, Request $request): RedirectResponse
    {
        $walletBefore = $withdrawal->wallet?->replicate();

        try {
            $this->walletService->unlockRejectedWithdrawal($withdrawal, $request->input('notes'));
        } catch (\InvalidArgumentException $e) {
            if ($withdrawal->fresh()->status === 'rejected') {
                return back()->with('status', __('Withdrawal already rejected.'));
            }

            return back()->with('error', $e->getMessage());
        }

        $withdrawal->refresh();
        $walletAfter = $withdrawal->wallet;

        $this->financialAudit->logMoneyAction(
            auth()->id(),
            'withdrawal.rejected',
            $withdrawal,
            $walletBefore,
            $walletAfter,
            $request->ip(),
            $request->userAgent(),
            $request->header('X-Request-Id'),
            FinancialAuditLog::sanitizeWithdrawal($withdrawal->only(['id', 'reference', 'amount', 'status', 'user_id'])),
        );

        return back()->with('status', __('Withdrawal rejected. Funds returned to available balance.'));
    }

    public function retry(Withdrawal $withdrawal, Request $request): RedirectResponse
    {
        try {
            $this->payouts->retryPayout($withdrawal, (int) auth()->id(), $request->ip());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', __('Payout retry submitted.'));
    }
}
