<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardMarketplaceComingSoon
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('marketplace.dashboard_coming_soon', true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->boolean('ajax')) {
            return response()->json([
                'coming_soon' => true,
                'message' => __('Marketplace is coming soon.'),
            ], 503);
        }

        return response()->view('dashboard.user.marketplace-coming-soon');
    }
}
