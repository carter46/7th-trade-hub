<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MarketplaceComingSoon
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if (! config('marketplace.public_coming_soon', true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->boolean('ajax')) {
            return response()->json([
                'coming_soon' => true,
                'html' => view('pages.marketplace-coming-soon')->render(),
                'url' => route('marketplace'),
            ]);
        }

        return response()->view('pages.marketplace-coming-soon');
    }
}
