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

        return view('dashboard.user.withdrawal.index', [
            'withdrawals' => $withdrawals,
            'wallet' => auth()->user()->wallet,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.user.withdrawal.create', [
            'wallet' => auth()->user()->wallet,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            return redirect()->route('dashboard.wallet')->with('error', __('Create a wallet first.'));
        }

        $withdrawalMin = (float) SystemSetting::get('withdrawal_min_amount', 100);
        $withdrawalMax = (float) SystemSetting::get('withdrawal_max_amount', 1000000);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$withdrawalMin, 'max:'.$withdrawalMax],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:20'],
            'account_name' => ['required', 'string', 'max:100'],
        ]);

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $validated['amount'],
            'currency' => 'NGN',
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'status' => 'pending',
            'reference' => 'WDR-'.strtoupper(Str::random(10)),
        ]);

        try {
            $this->walletService->lockForWithdrawal($withdrawal);
        } catch (\InvalidArgumentException $e) {
            $withdrawal->delete();

            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.withdrawal.index')
            ->with('status', __('Withdrawal request submitted.'));
    }
}
