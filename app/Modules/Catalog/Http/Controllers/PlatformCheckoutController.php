<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlatformCheckoutController extends Controller
{
    public function show(string $slug): RedirectResponse
    {
        return redirect()->route('dashboard.services.checkout', $slug);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        return redirect()
            ->route('dashboard.services.checkout', $slug)
            ->with('error', 'Please complete checkout from your dashboard.');
    }
}
