<?php

namespace App\Modules\Wallet\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Services\WalletProvisioningService;
use Illuminate\Http\RedirectResponse;

class WalletController extends Controller
{
    public function __construct(
        private WalletProvisioningService $provisioning
    ) {}

    public function create(): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasApprovedKyc()) {
            return redirect()->route('dashboard.kyc')->with('error', __('Complete KYC Level 1 before creating a wallet.'));
        }

        if ($user->wallet()->exists()) {
            return redirect()->route('dashboard.wallet')->with('status', __('You already have a wallet.'));
        }

        $this->provisioning->createWallet($user);

        return redirect()->route('dashboard.wallet')->with('status', __('Your wallet has been created.'));
    }
}
