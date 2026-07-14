<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingVersion;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Marketplace\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingAdminController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private NotificationService $notifications
    ) {}

    public function pending(): View
    {
        $listings = Listing::query()
            ->where(function ($query) {
                $query->where('status', 'pending_review')
                    ->orWhereHas('versions', fn ($v) => $v->where('status', 'pending_review'));
            })
            ->with(['user', 'versions'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('dashboard.admin.listings-pending', compact('listings'));
    }

    public function approve(Listing $listing, Request $request): RedirectResponse
    {
        if (! in_array($listing->status, ['pending_review', 'draft'], true)
            && ! $listing->versions()->where('status', 'pending_review')->exists()) {
            return back()->with('error', __('Listing is not pending review.'));
        }

        $version = $listing->versions()->where('status', 'pending_review')->latest()->first();

        if ($version) {
            $listing->update([
                'title' => $version->title,
                'description' => $version->description,
                'price' => $version->price,
                'status' => 'published',
                'is_active' => true,
            ]);
            $version->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        } else {
            $listing->update(['status' => 'published', 'is_active' => true]);
        }

        $this->audit->log(auth()->id(), 'listing.approved', $listing, null, $listing->toArray(), $request->ip());

        if ($listing->user) {
            $this->notifications->send(
                $listing->user,
                'listing',
                __('Listing published'),
                __('Your listing ":title" is now live on the marketplace.', ['title' => $listing->title]),
                route('marketplace.show', $listing->slug)
            );
        }

        return back()->with('status', __('Listing published.'));
    }

    public function reject(Listing $listing, Request $request): RedirectResponse
    {
        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        $version = $listing->versions()->where('status', 'pending_review')->latest('version_number')->first();
        if (! $version && $listing->status !== 'pending_review') {
            return back()->with('error', __('No pending version to reject.'));
        }

        if ($version) {
            $version->update([
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }

        if ($listing->status === 'pending_review') {
            $listing->update(['status' => 'rejected', 'is_active' => false]);
        }

        $this->audit->log(
            auth()->id(),
            'listing.rejected',
            $listing,
            null,
            ['notes' => $request->input('notes')],
            $request->ip()
        );

        if ($listing->user) {
            $this->notifications->send(
                $listing->user,
                'listing',
                __('Listing needs changes'),
                $request->input('notes') ?: __('Your listing submission was rejected. Edit and resubmit.'),
                route('dashboard.listings')
            );
        }

        return back()->with('status', __('Listing rejected. Seller can revise and resubmit.'));
    }
}
