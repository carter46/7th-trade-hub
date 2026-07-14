<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\WalletFunding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DepositController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $fundings = WalletFunding::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.user.deposit.index', [
            'fundings' => $fundings,
            'wallet' => $user->wallet,
        ]);
    }

    public function createBank(): View
    {
        return view('dashboard.user.deposit.bank', [
            'wallet' => auth()->user()->wallet,
        ]);
    }

    public function storeBank(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            return redirect()->route('dashboard.wallet')->with('error', __('Create a wallet first.'));
        }

        $depositMin = (float) SystemSetting::get('deposit_min_amount', 100);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$depositMin],
            'bank_name' => ['required', 'string', 'max:100'],
            'transfer_reference' => ['required', 'string', 'max:100'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('deposit-proofs', 'local');
        }

        WalletFunding::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'method' => 'bank',
            'amount' => $validated['amount'],
            'currency' => 'NGN',
            'status' => 'pending',
            'reference' => 'DEP-'.strtoupper(Str::random(10)),
            'metadata' => [
                'bank_name' => $validated['bank_name'],
                'transfer_reference' => $validated['transfer_reference'],
                'proof_path' => $proofPath,
            ],
        ]);

        return redirect()->route('dashboard.deposit.index')
            ->with('status', __('Deposit submitted. We will credit your wallet after verification.'));
    }
}
