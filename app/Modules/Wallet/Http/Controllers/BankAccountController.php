<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Payments\Contracts\PaymentRailInterface;
use App\Modules\Wallet\Services\BankAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function __construct(
        private BankAccountService $banks,
        private PaymentRailInterface $rail,
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        return view('dashboard.user.banks.index', [
            'bank' => $user->activeBankAccount,
            'canReplace' => ! $this->banks->hasOpenWithdrawal($user),
            'monnifyReady' => $this->rail->isConfigured(),
        ]);
    }

    public function replaceForm(): View|RedirectResponse
    {
        $user = auth()->user();
        try {
            $this->banks->assertCanReplace($user);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('dashboard.banks.index')->withErrors($e->errors());
        }

        return view('dashboard.user.banks.replace', [
            'bank' => $user->activeBankAccount,
            'step' => session('bank_replace_step', 'password'),
            'resolved' => session('bank_replace_resolved'),
            'banks' => $this->safeBanks(),
        ]);
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $this->banks->startReplace($request->user(), $validated['password']);

        return redirect()
            ->route('dashboard.banks.replace')
            ->with('bank_replace_step', 'otp')
            ->with('status', __('A verification code was sent to your email.'));
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $this->banks->verifyOtp($request->user(), $validated['otp']);

        return redirect()
            ->route('dashboard.banks.replace')
            ->with('bank_replace_step', 'bank')
            ->with('status', __('Email verified. Enter your new bank account.'));
    }

    public function resolve(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:20'],
        ]);

        try {
            $resolved = $this->banks->resolveNewBank(
                $request->user(),
                $validated['bank_code'],
                $validated['bank_name'],
                $validated['account_number'],
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('dashboard.banks.replace')
            ->with('bank_replace_step', 'confirm')
            ->with('bank_replace_resolved', $resolved);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:20'],
            // Name is re-resolved from Monnify on confirm; client value is ignored.
            'verified_name' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $this->banks->confirmReplace(
                $request->user(),
                $validated['bank_code'],
                $validated['bank_name'],
                $validated['account_number'],
                $validated['verified_name'] ?? null,
                $request->ip(),
                $request->userAgent(),
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('dashboard.banks.index')
            ->with('status', __('Withdrawal bank updated.'));
    }

    /**
     * @return list<array{name: string, code: string}>
     */
    private function safeBanks(): array
    {
        try {
            if ($this->rail->isConfigured()) {
                return $this->rail->listBanks();
            }
        } catch (\Throwable) {
            //
        }

        return [];
    }
}
