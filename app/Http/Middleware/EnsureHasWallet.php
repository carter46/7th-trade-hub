<?php

namespace App\Http\Middleware;

use App\Modules\Wallet\Services\WalletProvisioningService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasWallet
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->wallet) {
            if (! $user->hasApprovedKyc()) {
                return redirect()
                    ->route('dashboard.account.kyc')
                    ->with('error', __('Complete KYC Level 1 before using wallet features.'));
            }

            try {
                app(WalletProvisioningService::class)->createWallet($user);
                $user->load('wallet');
            } catch (\Throwable) {
                return redirect()
                    ->route('dashboard.wallet')
                    ->with('error', __('Create a wallet before using this feature.'));
            }
        }

        if (! $user->wallet) {
            return redirect()
                ->route('dashboard.wallet')
                ->with('error', __('Create a wallet before using this feature.'));
        }

        return $next($request);
    }
}
