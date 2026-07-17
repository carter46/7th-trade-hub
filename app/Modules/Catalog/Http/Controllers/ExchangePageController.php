<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use Illuminate\View\View;

class ExchangePageController extends Controller
{
    public function __invoke(): View
    {
        $rates = ExchangeRate::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        return view('pages.exchange', compact('rates'));
    }
}
