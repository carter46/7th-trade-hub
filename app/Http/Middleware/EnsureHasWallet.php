<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasWallet
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->wallet) {
            return redirect()
                ->route('dashboard.wallet')
                ->with('error', __('Create a wallet before using this feature.'));
        }

        return $next($request);
    }
}
