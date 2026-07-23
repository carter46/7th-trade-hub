<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingVersion;
use App\Models\MarketplaceProduct;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use App\Modules\Marketplace\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ListingAdminController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private NotificationService $notifications
    ) {}

    public function index(Request $request): View
    {
        $status = $request->get('status', 'active');
        if (! in_array($status, ['active', 'pending', 'suspended', 'rejected', 'sold', 'archived'], true)) {
            $status = 'active';
        }

        $query = Listing::query()
            ->with(['user', 'marketplaceProduct.category', 'versions']);

        $this->applyStatusFilter($query, $status);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->integer('category') ?: null) {
            $query->where(function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                    ->orWhereHas('marketplaceProduct', fn ($mp) => $mp->where('category_id', $categoryId));
            });
        }

        if ($productId = $request->integer('product') ?: null) {
            $query->where('marketplace_product_id', $productId);
        }

        if ($sellerId = $request->integer('seller') ?: null) {
            $query->where('user_id', $sellerId);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('updated_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('updated_at', '<=', $dateTo);
        }

        $listings = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $counts = [
            'active' => Listing::query()->where('status', 'published')->where('is_active', true)->count(),
            'pending' => Listing::query()->where(function ($q) {
                $q->where('status', 'pending_review')
                    ->orWhere(function ($inner) {
                        $inner->where('status', 'published')
                            ->whereHas('versions', fn ($v) => $v->where('status', 'pending_review'));
                    });
            })->whereNotIn('status', ['archived', 'sold'])->count(),
            'suspended' => Listing::query()->where('status', 'suspended')->count(),
            'rejected' => Listing::query()->where('status', 'rejected')->count(),
            'sold' => Listing::query()->where('status', 'sold')->count(),
            'archived' => Listing::query()->where('status', 'archived')->count(),
        ];

        $categories = Category::query()
            ->roots()
            ->where('type', 'marketplace')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $products = MarketplaceProduct::query()
            ->when($request->integer('category') ?: null, fn ($q, $catId) => $q->where('category_id', $catId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $sellers = User::whereHas('listings')->orderBy('name')->get();

        $filters = [
            'q' => $request->get('q'),
            'category' => $request->integer('category') ?: null,
            'product' => $request->integer('product') ?: null,
            'seller' => $request->integer('seller') ?: null,
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $data = compact(
            'listings',
            'counts',
            'status',
            'filters',
            'categories',
            'products',
            'sellers'
        );

        if ($request->headers->get('X-Dashboard-Tab') === '1') {
            return view('dashboard.admin.listings._panel', $data);
        }

        return view('dashboard.admin.listings', $data);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Listing>  $query
     */
    private function applyStatusFilter($query, string $status): void
    {
        match ($status) {
            'pending' => $query->where(function ($q) {
                $q->where('status', 'pending_review')
                    ->orWhere(function ($inner) {
                        $inner->where('status', 'published')
                            ->whereHas('versions', fn ($v) => $v->where('status', 'pending_review'));
                    });
            })->whereNotIn('status', ['archived', 'sold']),
            'suspended' => $query->where('status', 'suspended'),
            'rejected' => $query->where('status', 'rejected'),
            'sold' => $query->where('status', 'sold'),
            'archived' => $query->where('status', 'archived'),
            default => $query->where('status', 'published')->where('is_active', true),
        };
    }

    public function show(Listing $listing, Request $request): View
    {
        $listing->load([
            'user',
            'marketplaceProduct.category',
            'versions',
            'reviews.user',
            'orders',
        ]);

        $tab = $request->get('tab', 'overview');

        $auditLogs = [];
        if ($tab === 'audit') {
            $auditLogs = \App\Models\AuditLog::query()
                ->where('model_type', Listing::class)
                ->where('model_id', $listing->id)
                ->with('admin')
                ->orderByDesc('created_at')
                ->paginate(20);
        }

        return view('dashboard.admin.listings.show', compact('listing', 'tab', 'auditLogs'));
    }

    public function approve(Listing $listing, Request $request): RedirectResponse
    {
        $version = $listing->versions()->where('status', 'pending_review')->latest('version_number')->first();

        // Require a pending version, or a listing still in pending_review with no version row yet.
        if (! $version && $listing->status !== 'pending_review') {
            return back()->with('error', __('Listing is not pending review.'));
        }

        if ($version) {
            $listing->update([
                'title' => $version->title,
                'description' => $version->description,
                'price' => $version->price,
                // Version approved → listing published (auto-publish until scheduling ships)
                'status' => 'published',
                'is_active' => true,
            ]);
            $version->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        } else {
            // Legacy path: listing pending_review without a version row
            $listing->update(['status' => 'published', 'is_active' => true]);
            ListingVersion::create([
                'listing_id' => $listing->id,
                'version_number' => 1,
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
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

    public function suspend(Listing $listing, Request $request): RedirectResponse
    {
        if (! $listing->canBeSuspended()) {
            return back()->with('error', __('Only published or approved listings can be suspended.'));
        }

        $listing->update(['status' => 'suspended', 'is_active' => false]);

        $this->audit->log(
            auth()->id(),
            'listing.suspended',
            $listing,
            null,
            ['reason' => $request->input('reason')],
            $request->ip()
        );

        if ($listing->user) {
            $this->notifications->send(
                $listing->user,
                'listing',
                __('Listing suspended'),
                __('Your listing ":title" has been suspended.', ['title' => $listing->title]),
                route('dashboard.listings')
            );
        }

        return back()->with('status', __('Listing suspended.'));
    }

    public function restore(Listing $listing, Request $request): RedirectResponse
    {
        if ($listing->trashed()) {
            $listing->restore();
        }

        if ($listing->status === 'suspended') {
            $listing->update(['status' => 'published', 'is_active' => true]);
        } elseif ($listing->status === 'rejected') {
            // Rejected must go through a new review cycle — never publish directly.
            $listing->update(['status' => 'draft', 'is_active' => false]);
        } elseif ($listing->status === 'archived') {
            $listing->update(['status' => 'published', 'is_active' => true]);
        } else {
            return back()->with('error', __('Listing cannot be restored from its current status.'));
        }

        $this->audit->log(
            auth()->id(),
            'listing.restored',
            $listing,
            null,
            $listing->toArray(),
            $request->ip()
        );

        if ($listing->user) {
            $message = $listing->status === 'draft'
                ? __('Your listing ":title" was returned to draft. Edit and resubmit for review.', ['title' => $listing->title])
                : __('Your listing ":title" has been restored.', ['title' => $listing->title]);

            $this->notifications->send(
                $listing->user,
                'listing',
                __('Listing restored'),
                $message,
                $listing->status === 'published'
                    ? route('marketplace.show', $listing->slug)
                    : route('dashboard.listings')
            );
        }

        return back()->with('status', $listing->status === 'draft'
            ? __('Rejected listing returned to draft. Seller must resubmit for review.')
            : __('Listing restored.'));
    }

    public function toggleFeature(Listing $listing, Request $request): RedirectResponse
    {
        $listing->update(['featured' => ! $listing->featured]);

        $action = $listing->featured ? 'featured' : 'unfeatured';
        
        $this->audit->log(
            auth()->id(),
            "listing.{$action}",
            $listing,
            null,
            ['featured' => $listing->featured],
            $request->ip()
        );

        return back()->with('status', __('Listing ' . ($listing->featured ? 'featured' : 'unfeatured') . '.'));
    }

    public function duplicate(Listing $listing, Request $request): RedirectResponse
    {
        $newListing = $listing->replicate(['slug']);
        $newListing->slug = Str::slug($listing->title) . '-' . Str::random(6);
        $newListing->status = 'draft';
        $newListing->is_active = false;
        $newListing->featured = false;
        $newListing->save();

        ListingVersion::create([
            'listing_id' => $newListing->id,
            'version_number' => 1,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'status' => 'draft',
        ]);

        $this->audit->log(
            auth()->id(),
            'listing.duplicated',
            $newListing,
            null,
            ['original_id' => $listing->id],
            $request->ip()
        );

        return redirect()
            ->route('admin.listings.show', $newListing)
            ->with('status', __('Listing duplicated as draft.'));
    }

    public function destroy(Listing $listing, Request $request): RedirectResponse
    {
        if (! in_array($listing->status, ['suspended', 'rejected', 'archived'], true) && ! $listing->trashed()) {
            return back()->with('error', __('Only suspended, rejected, or archived listings can be deleted.'));
        }

        $listingData = $listing->toArray();
        $listingId = $listing->id;
        $force = $listing->trashed();

        if ($force) {
            $listing->forceDelete();
        } else {
            $listing->delete();
        }

        $this->audit->log(
            auth()->id(),
            'listing.deleted',
            null,
            $listingData,
            ['id' => $listingId, 'force' => $force],
            $request->ip()
        );

        return redirect()
            ->route('admin.listings')
            ->with('status', $force ? __('Listing permanently deleted.') : __('Listing deleted.'));
    }
}
