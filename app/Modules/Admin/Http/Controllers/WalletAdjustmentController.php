<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletAdjustmentController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private AuditLogService $audit
    ) {}

    public function create(): View
    {
        return view('dashboard.admin.wallet-adjustment');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_email' => ['required', 'email', 'exists:users,email'],
            'amount' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = User::where('email', $validated['user_email'])->firstOrFail();
        $wallet = $user->wallet;

        if (! $wallet) {
            return back()->withInput()->with('error', __('User does not have a wallet.'));
        }

        $transaction = $this->walletService->adminAdjust(
            $wallet,
            (float) $validated['amount'],
            $validated['reason'],
            auth()->id()
        );

        $this->audit->log(
            auth()->id(),
            'wallet.adjusted',
            $transaction,
            null,
            [
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
            ],
            $request->ip()
        );

        return back()->with('status', __('Wallet adjusted. New balance: ₦:balance', [
            'balance' => number_format((float) $wallet->fresh()->balance, 2),
        ]));
    }
}
