<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(): View
    {
        return view('dashboard.admin.settings', [
            'platformFeePercent' => SystemSetting::get('platform_fee_percent', '2.5'),
            'withdrawalMinAmount' => SystemSetting::get('withdrawal_min_amount', '100'),
            'withdrawalMaxAmount' => SystemSetting::get('withdrawal_max_amount', '1000000'),
            'depositMinAmount' => SystemSetting::get('deposit_min_amount', '100'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform_fee_percent' => ['required', 'numeric', 'min:0', 'max:50'],
            'withdrawal_min_amount' => ['required', 'numeric', 'min:1'],
            'withdrawal_max_amount' => ['required', 'numeric', 'min:1', 'gte:withdrawal_min_amount'],
            'deposit_min_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $old = [
            'platform_fee_percent' => SystemSetting::get('platform_fee_percent', '2.5'),
            'withdrawal_min_amount' => SystemSetting::get('withdrawal_min_amount', '100'),
            'withdrawal_max_amount' => SystemSetting::get('withdrawal_max_amount', '1000000'),
            'deposit_min_amount' => SystemSetting::get('deposit_min_amount', '100'),
        ];

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, (string) $value);
        }

        $this->audit->log(auth()->id(), 'settings.updated', null, $old, $validated, $request->ip());

        return back()->with('status', __('Platform settings saved.'));
    }
}
