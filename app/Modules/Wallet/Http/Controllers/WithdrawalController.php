<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\Withdrawal;
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function index(): View
    {
        $withdrawals = Withdrawal::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        $hasOpen = Withdrawal::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->whereIn('status', Withdrawal::OPEN_STATUSES)
                    ->orWhereIn('internal_status', Withdrawal::OPEN_INTERNAL);
            })
            ->exists();

        return view('dashboard.user.withdrawal.index', [
            'withdrawals' => $withdrawals,
            'wallet' => auth()->user()->wallet,
            'hasOpen' => $hasOpen,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $user = auth()->user();

        if (SystemSetting::kycRequired()) {
            $minLevel = SystemSetting::kycRequiredLevel('withdrawal', 1);
            if (! $user->hasApprovedKyc($minLevel)) {
                return redirect()->route('dashboard.withdrawal.index')
                    ->with('error', __('Complete KYC verification (level :level) before withdrawing.', [
                        'level' => $minLevel,
                    ]));
            }
        }

        $bank = $user->activeBankAccount;

        if (! $bank) {
            return redirect()->route('dashboard.banks.index')
                ->with('error', __('Add and verify a withdrawal bank before requesting a payout.'));
        }

        $hasOpen = Withdrawal::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereIn('status', Withdrawal::OPEN_STATUSES)
                    ->orWhereIn('internal_status', Withdrawal::OPEN_INTERNAL);
            })
            ->exists();

        if ($hasOpen) {
            return redirect()->route('dashboard.withdrawal.index')
                ->with('error', __('You already have a withdrawal in progress.'));
        }

        return view('dashboard.user.withdrawal.create', [
            'wallet' => $user->wallet,
            'bank' => $bank,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $bank = $user->activeBankAccount;

        if (! $wallet) {
            return redirect()->route('dashboard.wallet')->with('error', __('Create a wallet first.'));
        }

        if (SystemSetting::kycRequired()) {
            $minLevel = SystemSetting::kycRequiredLevel('withdrawal', 1);
            if (! $user->hasApprovedKyc($minLevel)) {
                return redirect()->route('dashboard.withdrawal.index')
                    ->with('error', __('Complete KYC verification (level :level) before withdrawing.', [
                        'level' => $minLevel,
                    ]));
            }
        }

        if (! $bank) {
            return redirect()->route('dashboard.banks.index')
                ->with('error', __('Add and verify a withdrawal bank first.'));
        }

        $hasOpen = Withdrawal::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereIn('status', Withdrawal::OPEN_STATUSES)
                    ->orWhereIn('internal_status', Withdrawal::OPEN_INTERNAL);
            })
            ->exists();

        if ($hasOpen) {
            return redirect()->route('dashboard.withdrawal.index')
                ->with('error', __('You already have a withdrawal in progress.'));
        }

        $withdrawalMin = (float) SystemSetting::get('withdrawal_min_amount', 100);
        $withdrawalMax = (float) SystemSetting::get('withdrawal_max_amount', 1000000);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$withdrawalMin, 'max:'.$withdrawalMax],
            'user_bank_account_id' => ['required', 'integer'],
        ]);

        if ((int) $validated['user_bank_account_id'] !== (int) $bank->id) {
            return back()->withInput()->with('error', __('Select your verified withdrawal bank.'));
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'user_bank_account_id' => $bank->id,
            'amount' => $validated['amount'],
            'currency' => 'NGN',
            'bank_name' => $bank->bank_name,
            'bank_code' => $bank->bank_code,
            'account_number' => $bank->account_number,
            'account_name' => $bank->verified_name,
            'status' => 'pending',
            'internal_status' => 'pending_review',
            'reference' => 'WDR-'.strtoupper(Str::random(10)),
        ]);

        try {
            $this->walletService->lockForWithdrawal($withdrawal);
        } catch (\InvalidArgumentException $e) {
            $withdrawal->delete();

            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.withdrawal.show', $withdrawal)
            ->with('status', __('Withdrawal request submitted.'));
    }

    public function show(Withdrawal $withdrawal): View
    {
        if ((int) $withdrawal->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $withdrawal->load('timelineEvents');

        return view('dashboard.user.withdrawal.show', [
            'withdrawal' => $withdrawal,
            'wallet' => auth()->user()->wallet,
        ]);
    }
}
