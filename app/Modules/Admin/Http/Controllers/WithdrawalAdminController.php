<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Admin\Services\FinancialAuditLog;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalAdminController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private AuditLogService $audit,
        private FinancialAuditLog $financialAudit,
    ) {}

    public function index(): View
    {
        $withdrawals = Withdrawal::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.withdrawals', compact('withdrawals'));
    }

    public function approve(Withdrawal $withdrawal, Request $request): RedirectResponse
    {
        $walletBefore = $withdrawal->wallet?->replicate();

        try {
            $this->walletService->debitForWithdrawal($withdrawal, auth()->id());
        } catch (\InvalidArgumentException $e) {
            if ($withdrawal->fresh()->status === 'completed') {
                return back()->with('status', __('Withdrawal already approved.'));
            }

            return back()->with('error', $e->getMessage());
        }

        $withdrawal->refresh();
        $walletAfter = $withdrawal->wallet;

        $this->financialAudit->logMoneyAction(
            auth()->id(),
            'withdrawal.approved',
            $withdrawal,
            $walletBefore,
            $walletAfter,
            $request->ip(),
            $request->userAgent(),
            $request->header('X-Request-Id'),
            FinancialAuditLog::sanitizeWithdrawal($withdrawal->only(['id', 'reference', 'amount', 'status', 'user_id'])),
        );

        return back()->with('status', __('Withdrawal approved and marked paid.'));
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
}
