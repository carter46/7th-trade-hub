<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\WalletFunding;
use App\Modules\Wallet\Services\DepositCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepositController extends Controller
{
    public function __construct(
        private DepositCheckoutService $checkout,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $fundings = WalletFunding::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.deposit.index', [
            'fundings' => $fundings,
            'wallet' => $user->wallet,
            'monnifyEnabled' => $this->checkout->monnifyEnabled(),
        ]);
    }

    public function createCheckout(): View|RedirectResponse
    {
        try {
            $this->checkout->assertDepositKyc(auth()->user());
        } catch (\Throwable $e) {
            return redirect()->route('dashboard.deposit.index')->with('error', $e->getMessage());
        }

        return view('dashboard.user.deposit.checkout', [
            'wallet' => auth()->user()->wallet,
            'depositMin' => (float) SystemSetting::get('deposit_min_amount', 100),
            'reservedAllowed' => $this->checkout->reservedAccountsAllowed(auth()->user()),
            'monnifyEnabled' => $this->checkout->monnifyEnabled(),
        ]);
    }

    public function storeCheckout(Request $request): RedirectResponse
    {
        $depositMin = (float) SystemSetting::get('deposit_min_amount', 100);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$depositMin],
        ]);

        try {
            $funding = $this->checkout->startCheckout(
                $request->user(),
                (float) $validated['amount'],
                route('dashboard.deposit.callback'),
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->away($funding->checkout_url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $paymentReference = (string) $request->query('paymentReference', $request->query('paymentReference', ''));
        if ($paymentReference === '') {
            $paymentReference = (string) $request->query('payment_reference', '');
        }

        if ($paymentReference === '') {
            return redirect()->route('dashboard.deposit.index')
                ->with('error', __('Missing payment reference.'));
        }

        try {
            $funding = $this->checkout->completeFromReturn($paymentReference);
        } catch (\Throwable $e) {
            return redirect()->route('dashboard.deposit.index')
                ->with('error', $e->getMessage());
        }

        if ($funding->internal_status === 'completed' || $funding->status === 'approved') {
            return redirect()->route('dashboard.deposit.show', $funding)
                ->with('status', __('Payment confirmed. Wallet credited.'));
        }

        return redirect()->route('dashboard.deposit.show', $funding)
            ->with('status', __('Payment received. Waiting for final confirmation.'));
    }

    public function show(WalletFunding $funding): View|RedirectResponse
    {
        if ((int) $funding->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $funding->load('timelineEvents');

        return view('dashboard.user.deposit.show', [
            'funding' => $funding,
            'wallet' => auth()->user()->wallet,
        ]);
    }

    public function reservedAccount(Request $request): RedirectResponse|View
    {
        try {
            $account = $this->checkout->ensureReservedAccount($request->user());
        } catch (\Throwable $e) {
            return redirect()->route('dashboard.deposit.create-checkout')
                ->with('error', $e->getMessage());
        }

        return view('dashboard.user.deposit.reserved', [
            'account' => $account,
            'wallet' => $request->user()->wallet,
        ]);
    }
}
