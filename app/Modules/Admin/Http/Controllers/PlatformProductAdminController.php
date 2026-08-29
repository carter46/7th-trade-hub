<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use App\Support\SortOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlatformProductAdminController extends Controller
{
    public function __construct(
        private MediaUsageService $mediaUsages,
        private MediaPathService $mediaPaths,
    ) {}

    public function index(Request $request): View
    {
        $products = PlatformProduct::query()
            ->with(['productType.serviceCategory'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                        ->orWhere('short_description', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->when($request->filled('service'), fn ($q) => $q->where('product_type_id', $request->integer('service')))
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->whereHas('productType', fn ($inner) => $inner->where('service_category_id', $request->integer('category')));
            })
            ->when($request->filled('type') && ! $request->filled('service'), function ($q) use ($request) {
                $q->ofType($request->string('type')->toString());
            })
            ->when($request->filled('featured'), function ($q) use ($request) {
                if ($request->get('featured') === '1') {
                    $q->where('is_featured', true);
                } elseif ($request->get('featured') === '0') {
                    $q->where('is_featured', false);
                }
            })
            ->orderBy('product_type_id')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.admin.platform-products', [
            'products' => $products,
            'types' => PlatformProductType::cases(),
            'serviceCategories' => ServiceCategory::query()->system()->orderBy('sort_order')->orderBy('name')->get(),
            'services' => ProductType::query()
                ->with('serviceCategory')
                ->whereHas('serviceCategory', fn ($q) => $q->system())
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->get('status'),
                'category' => $request->get('category'),
                'service' => $request->get('service'),
                'type' => $request->get('type'),
                'featured' => $request->get('featured'),
            ],
            'lockedCatalog' => true,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('admin.platform-products')
            ->with('error', __('Platform products are fixed. You cannot add new ones.'));
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('admin.platform-products')
            ->with('error', __('Platform products are fixed. You cannot add new ones.'));
    }

    public function edit(PlatformProduct $platformProduct): View|RedirectResponse
    {
        $platformProduct->load(['variants', 'productType.serviceCategory', 'heroMedia.variants']);

        if (! $platformProduct->productType?->serviceCategory?->isSystem()) {
            return redirect()
                ->route('admin.platform-products')
                ->with('error', __('That product is not under a fixed platform category.'));
        }

        return view('dashboard.admin.platform-product-form', [
            'product' => $platformProduct,
            'lockedCatalog' => true,
            'siblingMax' => PlatformProduct::query()
                ->where('product_type_id', $platformProduct->product_type_id)
                ->count(),
        ]);
    }

    public function update(Request $request, PlatformProduct $platformProduct): RedirectResponse
    {
        $platformProduct->loadMissing('productType.serviceCategory');
        if (! $platformProduct->productType?->serviceCategory?->isSystem()) {
            return redirect()
                ->route('admin.platform-products')
                ->with('error', __('That product is not under a fixed platform category.'));
        }

        $siblingMax = max(1, PlatformProduct::query()
            ->where('product_type_id', $platformProduct->product_type_id)
            ->count());

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in([
                PlatformProductStatus::Draft->value,
                PlatformProductStatus::Published->value,
            ])],
            'base_price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:'.$siblingMax],
            'hero_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['required', 'integer'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $heroMediaId = filled($data['hero_media_id'] ?? null) ? (int) $data['hero_media_id'] : null;
        $heroPath = $this->mediaPaths->legacyPathFromMediaId($heroMediaId);

        $platformProduct->update([
            'title' => $data['title'],
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'base_price' => $data['base_price'],
            'hero_media_id' => $heroMediaId,
            'hero_image' => $heroPath,
        ]);

        SortOrder::move(
            $platformProduct,
            (int) $data['sort_order'],
            PlatformProduct::query()->where('product_type_id', $platformProduct->product_type_id)
        );

        $this->updateExistingVariantPrices($platformProduct, $data['variants'] ?? []);
        $this->mediaUsages->syncUsages($platformProduct, [
            'hero' => $heroMediaId,
        ]);

        if ($data['status'] === PlatformProductStatus::Published->value) {
            $this->assertPublishable($platformProduct->fresh(['variants']));
        }

        return redirect()->route('admin.platform-products')->with('status', 'Product updated.');
    }

    public function destroy(): RedirectResponse
    {
        return redirect()
            ->route('admin.platform-products')
            ->with('error', __('Platform products cannot be deleted. Deactivate them instead.'));
    }

    /**
     * @param  list<array{id: int, price: mixed}>  $variants
     */
    private function updateExistingVariantPrices(PlatformProduct $product, array $variants): void
    {
        if ($variants === []) {
            return;
        }

        $existingIds = $product->variants()->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($variants as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0 || ! in_array($id, $existingIds, true)) {
                throw ValidationException::withMessages([
                    'variants' => 'Variant structure is fixed. You can only change prices of existing variants.',
                ]);
            }

            PlatformProductVariant::query()
                ->where('id', $id)
                ->where('platform_product_id', $product->id)
                ->update(['price' => (float) $row['price']]);
        }
    }

    private function assertPublishable(PlatformProduct $product): void
    {
        $hasActive = $product->variants()->where('is_active', true)->exists();
        if (! $hasActive && (float) $product->base_price <= 0) {
            throw ValidationException::withMessages([
                'status' => 'Published products require an active variant or a base price.',
            ]);
        }
        if ($product->variants()->exists() && ! $hasActive) {
            throw ValidationException::withMessages([
                'status' => 'Published products require at least one active variant.',
            ]);
        }
    }
}
