<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MarketplaceProduct;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use App\Support\FaqNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceCategoryAdminController extends Controller
{
    public function __construct(
        private MediaUsageService $mediaUsages,
        private MediaPathService $mediaPaths,
    ) {}

    public function index(): View
    {
        $categories = Category::query()
            ->marketplace()
            ->roots()
            ->with(['cardMedia.variants'])
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        // Accurate listing counts via products (single query map, no per-row N+1).
        $categoryIds = $categories->getCollection()->pluck('id');
        $listingCounts = \App\Models\Listing::query()
            ->selectRaw('marketplace_products.category_id as category_id, COUNT(listings.id) as aggregate')
            ->join('marketplace_products', 'marketplace_products.id', '=', 'listings.marketplace_product_id')
            ->whereIn('marketplace_products.category_id', $categoryIds)
            ->groupBy('marketplace_products.category_id')
            ->pluck('aggregate', 'category_id');

        $categories->getCollection()->transform(function (Category $category) use ($listingCounts) {
            $category->setAttribute('listings_via_products_count', (int) ($listingCounts[$category->id] ?? 0));

            return $category;
        });

        return view('dashboard.admin.marketplace-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('dashboard.admin.marketplace-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['type'] = 'marketplace';
        $data['parent_id'] = null;

        $category = Category::create($data);
        $this->syncMedia($category, $data);

        return redirect()
            ->route('admin.marketplace-categories')
            ->with('status', 'Marketplace category created.');
    }

    public function edit(Category $category): View
    {
        abort_unless($category->parent_id === null, 404);

        $category->load(['bannerMedia.variants', 'cardMedia.variants']);

        return view('dashboard.admin.marketplace-categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_unless($category->parent_id === null, 404);

        $data = $this->validated($request, $category->id);
        if (empty($data['slug'])) {
            $data['slug'] = $category->slug ?: Str::slug($data['name']);
        }
        $data['parent_id'] = null;
        $data['type'] = 'marketplace';

        $category->update($data);
        $this->syncMedia($category, $data);

        return redirect()
            ->route('admin.marketplace-categories')
            ->with('status', 'Marketplace category updated.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        abort_unless($category->parent_id === null, 404);

        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('status', 'Category '.($category->is_active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_unless($category->parent_id === null && $category->type === 'marketplace', 404);

        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete a category that still has marketplace products. Move or delete products first.');
        }

        if ($category->listings()->exists()) {
            return back()->with('error', 'Cannot delete a category that still has listings attached.');
        }

        $this->mediaUsages->detachAllFor($category);
        $category->delete();

        return redirect()
            ->route('admin.marketplace-categories')
            ->with('status', 'Marketplace category deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($ignoreId),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['nullable', 'string', 'max:500'],
            'faq' => ['nullable', 'array'],
            'faq.*.q' => ['nullable', 'string', 'max:500'],
            'faq.*.a' => ['nullable', 'string', 'max:2000'],
            'faq.*.open' => ['nullable', 'boolean'],
            'card_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
            'icon' => ['nullable', 'string', 'max:80'],
        ]);

        $cardMediaId = isset($data['card_media_id']) ? (int) $data['card_media_id'] : null;
        $path = $this->mediaPaths->legacyPathFromMediaId($cardMediaId);

        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'short_description' => $data['short_description'] ?? null,
            'hero_title' => $data['hero_title'] ?? null,
            'hero_subtitle' => $data['hero_subtitle'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'benefits' => FaqNormalizer::stringList($data['benefits'] ?? null),
            'faq' => FaqNormalizer::fromRequest($data['faq'] ?? null),
            'icon' => $data['icon'] ?? null,
            'card_media_id' => $cardMediaId,
            'banner_media_id' => $cardMediaId,
            'card_image' => $path,
            'banner_image' => $path,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMedia(Category $category, array $data): void
    {
        $mediaId = $data['card_media_id'] ?? null;
        $this->mediaUsages->syncUsages($category, [
            'card' => $mediaId,
            'banner' => $mediaId,
        ]);
    }
}
