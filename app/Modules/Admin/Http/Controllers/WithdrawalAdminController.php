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
use Illuminate\Support\Facades\Log;
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
        Log::info('admin.withdrawal.approve.request', [
            'withdrawal_id' => $withdrawal->id,
            'reference' => $withdrawal->reference,
            'admin_id' => auth()->id(),
            'status' => $withdrawal->status,
            'internal_status' => $withdrawal->internal_status,
        ]);

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
            Log::error('admin.withdrawal.approve.failed', [
                'withdrawal_id' => $withdrawal->id,
                'reference' => $withdrawal->reference,
                'admin_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);
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

        $msg = match (true) {
            $withdrawal->internal_status === 'completed' => __('Withdrawal approved and completed.'),
            $withdrawal->needsProviderAuthorization() => __('Withdrawal sent to Monnify. Enter the Monnify OTP on the withdrawal detail page to authorize the transfer.'),
            default => __('Withdrawal approved and sent to payment provider. Waiting for payout confirmation.'),
        };

        Log::info('admin.withdrawal.approve.success', [
            'withdrawal_id' => $withdrawal->id,
            'reference' => $withdrawal->reference,
            'status' => $withdrawal->status,
            'internal_status' => $withdrawal->internal_status,
        ]);

        if ($withdrawal->needsProviderAuthorization()) {
            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('status', $msg);
        }

        return back()->with('status', $msg);
    }

    public function show(Withdrawal $withdrawal): View
    {
        if ($withdrawal->needsProviderAuthorization() && ! $withdrawal->isTerminal()) {
            $withdrawal = $this->payouts->refreshProviderStatus($withdrawal);
        }

        $withdrawal->load(['user', 'approver', 'timelineEvents']);

        $merchantBalance = null;
        if ($this->rail->isConfigured()) {
            try {
                $merchantBalance = $this->payouts->merchantBalance();
            } catch (\Throwable) {
                $merchantBalance = null;
            }
        }

        return view('dashboard.admin.withdrawals.show', [
            'withdrawal' => $withdrawal,
            'merchantBalance' => $merchantBalance,
            'monnifyEnabled' => $this->rail->isConfigured(),
            'maxAuthAttempts' => 5,
        ]);
    }

    public function authorizeProvider(Withdrawal $withdrawal, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'authorization_code' => ['required', 'string', 'min:4', 'max:12'],
        ]);

        try {
            $this->payouts->authorizeProviderTransfer(
                $withdrawal,
                $validated['authorization_code'],
                (int) auth()->id(),
            );
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('error', $e->getMessage());
        }

        $withdrawal->refresh();
        $msg = $withdrawal->status === 'completed'
            ? __('Monnify transfer authorized and withdrawal completed.')
            : __('Monnify OTP accepted. Payout is processing — status will update when the provider confirms.');

        return redirect()
            ->route('admin.withdrawals.show', $withdrawal)
            ->with('status', $msg);
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
