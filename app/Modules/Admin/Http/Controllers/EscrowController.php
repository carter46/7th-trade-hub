<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\SystemSetting;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Admin\Services\FinancialAuditLog;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EscrowController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private AuditLogService $audit,
        private FinancialAuditLog $financialAudit,
    ) {}

    public function index(): View
    {
        $escrows = Escrow::with(['order.user', 'order.listing'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.escrows', compact('escrows'));
    }

    public function release(Escrow $escrow, Request $request): RedirectResponse
    {
        if ($escrow->status === 'released') {
            return back()->with('status', __('Escrow already released.'));
        }

        if ($escrow->status !== 'locked') {
            return back()->with('error', __('Escrow is not locked.'));
        }

        $buyerWalletBefore = $escrow->buyerWallet?->replicate();
        $feePercent = (float) SystemSetting::get('platform_fee_percent', 2.5);

        try {
            $this->walletService->releaseEscrow($escrow, auth()->id(), $feePercent);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $escrow->order?->update(['status' => 'completed']);
        $escrow->refresh();

        $this->financialAudit->logMoneyAction(
            auth()->id(),
            'escrow.released',
            $escrow,
            $buyerWalletBefore,
            $escrow->buyerWallet,
            $request->ip(),
            $request->userAgent(),
            $request->header('X-Request-Id'),
        );

        return back()->with('status', __('Escrow released.'));
    }

    public function refund(Escrow $escrow, Request $request): RedirectResponse
    {
        if (in_array($escrow->status, ['refunded', 'partial_refund'], true)) {
            return back()->with('status', __('Escrow already refunded.'));
        }

        if ($escrow->status !== 'locked') {
            return back()->with('error', __('Escrow is not locked.'));
        }

        $buyerWalletBefore = $escrow->buyerWallet?->replicate();

        try {
            $this->walletService->refundEscrow($escrow, null, $request->input('reason', 'Admin refund'));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $escrow->order?->update(['status' => 'cancelled']);
        $escrow->refresh();

        $this->financialAudit->logMoneyAction(
            auth()->id(),
            'escrow.refunded',
            $escrow,
            $buyerWalletBefore,
            $escrow->buyerWallet,
            $request->ip(),
            $request->userAgent(),
            $request->header('X-Request-Id'),
        );

        return back()->with('status', __('Escrow refunded to buyer.'));
    }
}
