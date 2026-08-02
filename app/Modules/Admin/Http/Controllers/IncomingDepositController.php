<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\IncomingCryptoTransaction;
use App\Modules\Wallet\Services\Blockchain\DepositMatchingService;
use App\Modules\Wallet\Services\CryptoExplorerUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomingDepositController extends Controller
{
    public function index(): View
    {
        $deposits = IncomingCryptoTransaction::query()
            ->with('matchedOrder.user')
            ->orderByDesc('detected_at')
            ->paginate(30);

        return view('dashboard.admin.incoming-deposits.index', [
            'deposits' => $deposits,
            'explorer' => CryptoExplorerUrl::class,
        ]);
    }

    public function ignore(IncomingCryptoTransaction $incomingCryptoTransaction, Request $request): RedirectResponse
    {
        $incomingCryptoTransaction->update(['status' => IncomingCryptoTransaction::STATUS_IGNORED]);

        return back()->with('status', __('Deposit marked ignored.'));
    }

    public function rematch(IncomingCryptoTransaction $incomingCryptoTransaction, DepositMatchingService $matcher): RedirectResponse
    {
        if ($incomingCryptoTransaction->matched_order_id) {
            return back()->with('status', __('Already matched.'));
        }

        $ok = $matcher->tryMatch($incomingCryptoTransaction);

        return back()->with($ok ? 'status' : 'error', $ok ? __('Matched.') : __('Still unmatched.'));
    }
}
