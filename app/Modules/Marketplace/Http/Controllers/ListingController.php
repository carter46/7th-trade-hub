<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function create(): View
    {
        $parents = Category::query()
            ->marketplace()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('dashboard.user.listings-create', [
            'parents' => $parents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('type', 'marketplace')->whereNull('parent_id')->where('is_active', true)),
            ],
            'marketplace_product_id' => ['required', 'exists:marketplace_products,id'],
        ]);

        $product = \App\Models\MarketplaceProduct::with('category')->findOrFail($validated['marketplace_product_id']);

        if (! $product->is_active) {
            return back()->withInput()->withErrors(['marketplace_product_id' => 'Selected product is not active.']);
        }

        if ($product->category_id !== (int) $validated['category_id']) {
            return back()->withInput()->withErrors(['marketplace_product_id' => 'Selected product does not belong to the chosen category.']);
        }

        if (! $product->category?->is_active) {
            return back()->withInput()->withErrors(['category_id' => 'Selected category is not active.']);
        }

        $listing = Listing::create([
            'user_id' => auth()->id(),
            'marketplace_product_id' => $product->id,
            'category_id' => $product->category_id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::random(6),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category' => $product->slug,
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

        $parents = Category::query()
            ->marketplace()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $selectedCategoryId = $listing->marketplaceProduct?->category_id ?? 0;
        $selectedProductId = $listing->marketplace_product_id ?? 0;

        return view('dashboard.user.listings-edit', [
            'listing' => $listing,
            'version' => $version,
            'parents' => $parents,
            'selectedCategoryId' => (int) $selectedCategoryId,
            'selectedProductId' => (int) $selectedProductId,
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
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('type', 'marketplace')->whereNull('parent_id')->where('is_active', true)),
            ],
            'marketplace_product_id' => ['required', 'exists:marketplace_products,id'],
        ]);

        $product = \App\Models\MarketplaceProduct::with('category')->findOrFail($validated['marketplace_product_id']);

        if (! $product->is_active) {
            return back()->withInput()->withErrors(['marketplace_product_id' => 'Selected product is not active.']);
        }

        if ($product->category_id !== (int) $validated['category_id']) {
            return back()->withInput()->withErrors(['marketplace_product_id' => 'Selected product does not belong to the chosen category.']);
        }

        if (! $product->category?->is_active) {
            return back()->withInput()->withErrors(['category_id' => 'Selected category is not active.']);
        }

        $version->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
        ]);

        if ($listing->status !== 'published') {
            $listing->update([
                'marketplace_product_id' => $product->id,
                'category_id' => $product->category_id,
                'category' => $product->slug,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'price' => $validated['price'],
            ]);
        } else {
            $listing->update([
                'marketplace_product_id' => $product->id,
                'category_id' => $product->category_id,
                'category' => $product->slug,
            ]);
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

    public function archive(Listing $listing): RedirectResponse
    {
        $this->authorize('update', $listing);

        if (! in_array($listing->status, ['published', 'suspended'], true)) {
            return back()->with('error', __('Only published or suspended listings can be archived.'));
        }

        $listing->update([
            'status' => 'archived',
            'is_active' => false,
        ]);

        return back()->with('status', __('Listing archived.'));
    }

    public function restoreArchive(Listing $listing): RedirectResponse
    {
        $this->authorize('update', $listing);

        if ($listing->status !== 'archived') {
            return back()->with('error', __('Only archived listings can be restored from archive.'));
        }

        $listing->update([
            'status' => 'draft',
            'is_active' => false,
        ]);

        return back()->with('status', __('Listing restored to draft. Edit and submit for review.'));
    }

    private function editableVersion(Listing $listing): ?ListingVersion
    {
        return $listing->versions()
            ->whereIn('status', ['draft', 'rejected'])
            ->orderByDesc('version_number')
            ->first();
    }
}
