<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CatalogPageContent;
use App\Models\Category;
use App\Models\ExchangeRate;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogMetaAdminController extends Controller
{
    public function __construct(
        private MediaUsageService $mediaUsages,
        private MediaPathService $mediaPaths,
    ) {}
    public function marketplaceCategories(): View
    {
        $categories = Category::query()
            ->with('parent')
            ->withCount('listings')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('dashboard.admin.marketplace-categories.index', compact('categories'));
    }

    public function createMarketplaceCategory(): View
    {
        $parents = Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('dashboard.admin.marketplace-categories.create', compact('parents'));
    }

    public function storeMarketplaceCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'parent_id' => $data['parent_id'] ?? null,
            'type' => 'marketplace',
            'is_active' => true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.marketplace-categories')
            ->with('status', 'Category created.');
    }

    public function editMarketplaceCategory(Category $category): View
    {
        $parents = Category::query()
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('dashboard.admin.marketplace-categories.edit', compact('category', 'parents'));
    }

    public function updateMarketplaceCategory(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                Rule::notIn([$category->id]),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! empty($data['parent_id'])) {
            $parent = Category::find($data['parent_id']);
            if ($parent && $parent->parent_id) {
                return back()->withInput()->with('error', 'Only top-level categories can be parents.');
            }
        }

        $category->update([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.marketplace-categories')
            ->with('status', 'Category updated.');
    }

    public function platformCategories(): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function createPlatformCategory(): RedirectResponse
    {
        return redirect()->route('admin.service-categories.create');
    }

    public function storePlatformCategory(Request $request): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function editPlatformCategory($platformCategory = null): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function updatePlatformCategory(Request $request, $platformCategory = null): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function togglePlatformCategory($platformCategory = null): RedirectResponse
    {
        return redirect()->route('admin.service-categories');
    }

    public function toggleMarketplaceCategory(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('status', 'Category '.($category->is_active ? 'activated' : 'deactivated').'.');
    }

    public function catalogPages(): View
    {
        $pages = CatalogPageContent::query()
            ->with(['bannerMedia', 'cardMedia'])
            ->orderBy('scope')
            ->orderBy('key')
            ->get()
            ->keyBy(fn ($row) => $row->scope.'.'.$row->key);

        $keys = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('service_categories') && \App\Models\ServiceCategory::query()->exists()) {
            foreach (\App\Models\ServiceCategory::query()->orderBy('sort_order')->get(['slug', 'name']) as $category) {
                $keys[] = ['scope' => 'group', 'key' => $category->slug, 'label' => $category->name];
            }
            foreach (\App\Models\ProductType::query()->orderBy('sort_order')->get(['slug', 'name']) as $service) {
                $keys[] = ['scope' => 'type', 'key' => $service->slug, 'label' => $service->name];
            }
        } else {
            foreach (array_keys(config('catalog.groups', [])) as $slug) {
                $keys[] = ['scope' => 'group', 'key' => $slug, 'label' => config('catalog.groups.'.$slug.'.label', $slug)];
            }
            foreach (array_keys(config('catalog.types', [])) as $type) {
                $keys[] = ['scope' => 'type', 'key' => $type, 'label' => config('catalog.types.'.$type.'.label', $type)];
            }
        }

        return view('dashboard.admin.catalog-pages', compact('pages', 'keys'));
    }

    public function upsertCatalogPage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'scope' => ['required', Rule::in(['group', 'type'])],
            'key' => ['required', 'string', 'max:80'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'banner_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
            'card_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
        ]);

        if ($data['scope'] === 'group') {
            $knownGroup = isset(config('catalog.groups')[$data['key']])
                || (\Illuminate\Support\Facades\Schema::hasTable('service_categories')
                    && \App\Models\ServiceCategory::query()->where('slug', $data['key'])->exists());
            if (! $knownGroup) {
                return back()->withErrors(['key' => 'Unknown group key.']);
            }
        }
        if ($data['scope'] === 'type') {
            $knownType = isset(config('catalog.types')[$data['key']])
                || (\Illuminate\Support\Facades\Schema::hasTable('product_types')
                    && \App\Models\ProductType::query()->where('slug', $data['key'])->exists());
            if (! $knownType) {
                return back()->withErrors(['key' => 'Unknown type key.']);
            }
        }

        $bannerMediaId = isset($data['banner_media_id']) ? (int) $data['banner_media_id'] : null;
        $cardMediaId = isset($data['card_media_id']) ? (int) $data['card_media_id'] : null;

        $page = CatalogPageContent::updateOrCreate(
            ['scope' => $data['scope'], 'key' => $data['key']],
            [
                'short_description' => ($data['short_description'] ?? null) ?: null,
                'hero_title' => ($data['hero_title'] ?? null) ?: null,
                'hero_subtitle' => ($data['hero_subtitle'] ?? null) ?: null,
                'banner_media_id' => $bannerMediaId,
                'card_media_id' => $cardMediaId,
                'banner_image' => $this->mediaPaths->legacyPathFromMediaId($bannerMediaId, 'large'),
                'card_image' => $this->mediaPaths->legacyPathFromMediaId($cardMediaId, 'medium'),
            ]
        );

        $this->mediaUsages->syncUsages($page, [
            'banner' => $data['banner_media_id'] ?? null,
            'card' => $data['card_media_id'] ?? null,
        ]);

        return back()->with('status', 'Catalog page content saved (overrides config defaults where set).');
    }

    public function exchangeRates(): View
    {
        return view('dashboard.admin.exchange-rates.index', [
            'rates' => ExchangeRate::orderBy('sort_order')->paginate(20),
        ]);
    }

    public function createExchangeRate(): View
    {
        return view('dashboard.admin.exchange-rates.create');
    }

    public function storeExchangeRate(Request $request): RedirectResponse
    {
        $data = $this->validatedExchangeRate($request);

        ExchangeRate::create([
            'asset' => strtoupper($data['asset']),
            'buy_rate_ngn' => $data['buy_rate_ngn'],
            'sell_rate_ngn' => $data['sell_rate_ngn'],
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'maximum_amount' => $data['maximum_amount'] ?? null,
            'processing_time' => $data['processing_time'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.exchange-rates')
            ->with('status', 'Exchange rate created.');
    }

    public function editExchangeRate(ExchangeRate $exchangeRate): View
    {
        return view('dashboard.admin.exchange-rates.edit', [
            'rate' => $exchangeRate,
        ]);
    }

    public function updateExchangeRate(Request $request, ExchangeRate $exchangeRate): RedirectResponse
    {
        $data = $this->validatedExchangeRate($request, $exchangeRate);

        $exchangeRate->update([
            'asset' => strtoupper($data['asset']),
            'buy_rate_ngn' => $data['buy_rate_ngn'],
            'sell_rate_ngn' => $data['sell_rate_ngn'],
            'minimum_amount' => $data['minimum_amount'] ?? null,
            'maximum_amount' => $data['maximum_amount'] ?? null,
            'processing_time' => $data['processing_time'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.exchange-rates')
            ->with('status', 'Exchange rate updated.');
    }

    public function destroyExchangeRate(ExchangeRate $exchangeRate): RedirectResponse
    {
        $exchangeRate->delete();

        return redirect()
            ->route('admin.exchange-rates')
            ->with('status', 'Rate deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedExchangeRate(Request $request, ?ExchangeRate $exchangeRate = null): array
    {
        return $request->validate([
            'asset' => [
                'required',
                'string',
                'max:20',
                Rule::unique('exchange_rates', 'asset')->ignore($exchangeRate?->id),
            ],
            'buy_rate_ngn' => ['required', 'numeric', 'min:0'],
            'sell_rate_ngn' => ['required', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'processing_time' => ['nullable', 'string', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
