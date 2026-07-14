<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function create(): View
    {
        return view('dashboard.user.listings-create', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        $listing = Listing::create([
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::random(6),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'status' => 'draft',
            'is_active' => false,
        ]);

        ListingVersion::create([
            'listing_id' => $listing->id,
            'version_number' => 1,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'status' => 'draft',
        ]);

        return redirect()->route('dashboard.listings')->with('status', __('Listing draft created.'));
    }

    public function edit(Listing $listing): View|RedirectResponse
    {
        $this->authorize('update', $listing);

        $version = $this->editableVersion($listing);
        if (! $version) {
            return redirect()->route('dashboard.listings')
                ->with('error', __('Create a new revision before editing a published listing.'));
        }

        return view('dashboard.user.listings-edit', [
            'listing' => $listing,
            'version' => $version,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorize('update', $listing);

        $version = $this->editableVersion($listing);
        if (! $version) {
            return back()->with('error', __('No editable draft found.'));
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        $version->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
        ]);

        if ($listing->status !== 'published') {
            $listing->update([
                'category_id' => $validated['category_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'price' => $validated['price'],
            ]);
        } else {
            $listing->update(['category_id' => $validated['category_id'] ?? $listing->category_id]);
        }

        return redirect()->route('dashboard.listings')->with('status', __('Listing draft updated.'));
    }

    public function storeRevision(Listing $listing): RedirectResponse
    {
        $this->authorize('update', $listing);

        if (! in_array($listing->status, ['published', 'rejected'], true)) {
            return back()->with('error', __('Revisions are only for published or rejected listings.'));
        }

        if ($listing->versions()->whereIn('status', ['draft', 'pending_review'])->exists()) {
            return back()->with('error', __('You already have a draft or pending revision.'));
        }

        $latest = $listing->versions()->orderByDesc('version_number')->first();
        $nextNumber = ($latest?->version_number ?? 0) + 1;

        ListingVersion::create([
            'listing_id' => $listing->id,
            'version_number' => $nextNumber,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'status' => 'draft',
        ]);

        if ($listing->status === 'rejected') {
            $listing->update(['status' => 'draft', 'is_active' => false]);
        }

        return redirect()->route('dashboard.listings.edit', $listing)
            ->with('status', __('New revision created. Edit and submit for review.'));
    }

    public function submitForReview(Listing $listing): RedirectResponse
    {
        $this->authorize('submitForReview', $listing);

        $version = $listing->versions()
            ->whereIn('status', ['draft', 'rejected'])
            ->orderByDesc('version_number')
            ->first();

        if (! $version) {
            return back()->with('error', __('No draft version to submit.'));
        }

        $version->update(['status' => 'pending_review', 'submitted_at' => now()]);

        if ($listing->status !== 'published') {
            $listing->update(['status' => 'pending_review']);
        }

        return back()->with('status', __('Listing submitted for admin review.'));
    }

    private function editableVersion(Listing $listing): ?ListingVersion
    {
        return $listing->versions()
            ->whereIn('status', ['draft', 'rejected'])
            ->orderByDesc('version_number')
            ->first();
    }
}
