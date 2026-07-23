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

class MarketplaceProductAdminController extends Controller
{
    public function __construct(
        private MediaUsageService $mediaUsages,
        private MediaPathService $mediaPaths,
    ) {}

    public function index(Request $request): View
    {
        $products = MarketplaceProduct::query()
            ->with(['category', 'cardMedia.variants'])
            ->withCount('listings')
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)->orWhere('slug', 'like', $term);
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::query()->marketplace()->roots()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('dashboard.admin.marketplace-products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::query()->marketplace()->roots()->active()->orderBy('sort_order')->orderBy('name')->get();

        return view('dashboard.admin.marketplace-products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $product = MarketplaceProduct::create($data);
        $this->syncMedia($product, $data);

        return redirect()
            ->route('admin.marketplace-products')
            ->with('status', 'Marketplace product created.');
    }

    public function edit(MarketplaceProduct $marketplaceProduct): View
    {
        $marketplaceProduct->load(['bannerMedia.variants', 'cardMedia.variants', 'category']);
        $categories = Category::query()->marketplace()->roots()->orderBy('sort_order')->orderBy('name')->get();

        return view('dashboard.admin.marketplace-products.edit', [
            'product' => $marketplaceProduct,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, MarketplaceProduct $marketplaceProduct): RedirectResponse
    {
        $data = $this->validated($request, $marketplaceProduct->id);
        if (empty($data['slug'])) {
            $data['slug'] = $marketplaceProduct->slug ?: Str::slug($data['name']);
        }

        $marketplaceProduct->update($data);
        $this->syncMedia($marketplaceProduct, $data);

        return redirect()
            ->route('admin.marketplace-products')
            ->with('status', 'Marketplace product updated.');
    }

    public function toggle(MarketplaceProduct $marketplaceProduct): RedirectResponse
    {
        $marketplaceProduct->update(['is_active' => ! $marketplaceProduct->is_active]);

        return back()->with('status', 'Product '.($marketplaceProduct->is_active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(MarketplaceProduct $marketplaceProduct): RedirectResponse
    {
        if ($marketplaceProduct->listings()->exists()) {
            return back()->with('error', 'Cannot delete a product that still has listings. Reassign or remove listings first.');
        }

        $this->mediaUsages->detachAllFor($marketplaceProduct);
        $marketplaceProduct->delete();

        return redirect()
            ->route('admin.marketplace-products')
            ->with('status', 'Marketplace product deleted.');
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
                Rule::unique('marketplace_products', 'slug')->ignore($ignoreId),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('type', 'marketplace')->whereNull('parent_id')),
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
            'category_id' => (int) $data['category_id'],
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
    private function syncMedia(MarketplaceProduct $product, array $data): void
    {
        $mediaId = $data['card_media_id'] ?? null;
        $this->mediaUsages->syncUsages($product, [
            'card' => $mediaId,
            'banner' => $mediaId,
        ]);
    }
}
