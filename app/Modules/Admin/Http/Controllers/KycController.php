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

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'pending';
        if (! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $query = KycSubmission::with('user')->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $search = trim($request->string('q')->toString());

        if ($search !== '') {
            $query->whereHas('user', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where('email', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('username', 'like', $search.'%');
            });
        }

        $submissions = $query->paginate(20)->withQueryString();

        $counts = [
            'pending' => KycSubmission::where('status', 'pending')->count(),
            'approved' => KycSubmission::where('status', 'approved')->count(),
            'rejected' => KycSubmission::where('status', 'rejected')->count(),
        ];

        $data = compact('submissions', 'status', 'counts', 'search');

        if ($this->wantsTabPartial($request)) {
            return view('dashboard.admin.kyc._panel', $data);
        }

        return view('dashboard.admin.kyc', $data);
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

    public function returnToPending(KycSubmission $submission, Request $request): RedirectResponse
    {
        if ($submission->status === 'pending') {
            return back()->with('error', __('Submission is already pending.'));
        }

        $old = $submission->status;
        $submission->update([
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $this->audit->log(
            auth()->id(),
            'kyc.returned_to_pending',
            $submission,
            ['status' => $old],
            ['status' => 'pending'],
            $request->ip()
        );

        return back()->with('status', __('KYC returned to pending queue.'));
    }

    public function override(KycSubmission $submission, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kyc_level' => ['required', 'integer', 'min:0', 'max:3'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        User::where('id', $submission->user_id)->update([
            'kyc_level' => $validated['kyc_level'],
        ]);

        $submission->update([
            'level_granted' => $validated['kyc_level'],
            'notes' => $validated['notes'] ?? $submission->notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->audit->log(
            auth()->id(),
            'kyc.override',
            $submission,
            null,
            ['kyc_level' => $validated['kyc_level']],
            $request->ip(),
            ['reason' => $validated['notes'] ?? null]
        );

        return back()->with('status', __('KYC level overridden.'));
    }

    private function wantsTabPartial(Request $request): bool
    {
        return $request->header('X-Dashboard-Tab') === '1'
            || $request->boolean('partial');
    }
}
