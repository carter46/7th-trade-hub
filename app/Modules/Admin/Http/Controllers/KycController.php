<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KycController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(): View
    {
        $submissions = KycSubmission::with('user')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('dashboard.admin.kyc', compact('submissions'));
    }

    public function approve(KycSubmission $submission, Request $request): RedirectResponse
    {
        if ($submission->status !== 'pending') {
            return back()->with('error', __('Only pending KYC submissions can be approved.'));
        }

        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);
        $submission->update([
            'status' => 'approved',
            'level_granted' => $submission->level_requested,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        User::where('id', $submission->user_id)->update([
            'kyc_level' => $submission->level_requested,
        ]);

        $this->audit->log(auth()->id(), 'kyc.approved', $submission, null, $submission->toArray(), $request->ip());

        return back()->with('status', __('KYC approved.'));
    }

    public function reject(KycSubmission $submission, Request $request): RedirectResponse
    {
        if ($submission->status !== 'pending') {
            return back()->with('error', __('Only pending KYC submissions can be rejected.'));
        }

        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);
        $submission->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        $this->audit->log(auth()->id(), 'kyc.rejected', $submission, null, $submission->toArray(), $request->ip());

        return back()->with('status', __('KYC rejected.'));
    }
}
