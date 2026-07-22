<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Models\PlatformProduct;
use App\Models\PlatformProductImage;
use App\Models\PlatformProductVariant;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use App\Support\FaqNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.admin.platform-products', [
            'products' => $products,
            'types' => PlatformProductType::cases(),
            'serviceCategories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'services' => ProductType::query()->with('serviceCategory')->orderBy('sort_order')->orderBy('name')->get(),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->get('status'),
                'category' => $request->get('category'),
                'service' => $request->get('service'),
                'type' => $request->get('type'),
                'featured' => $request->get('featured'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.platform-product-form', [
            'product' => new PlatformProduct([
                'status' => PlatformProductStatus::Draft,
                'base_price' => 0,
                'provider' => 'manual',
                'fulfillment_mode' => 'manual',
                'auto_renew' => false,
            ]),
            'galleryMediaIds' => [],
            'serviceCategories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'services' => ProductType::query()->with('serviceCategory')->orderBy('sort_order')->orderBy('name')->get(),
            'types' => PlatformProductType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $galleryIds = $data['gallery_media_ids'];
        unset($data['gallery_media_ids']);

        $product = PlatformProduct::create($data);
        $this->syncVariants($product, $request->input('variants', []), (float) $data['base_price']);
        $this->syncGallery($product, $galleryIds);
        $this->assertPublishable($product, $data['status']);

        return redirect()->route('admin.platform-products')->with('status', 'Product created.');
    }

    public function edit(PlatformProduct $platformProduct): View
    {
        $platformProduct->load(['variants', 'images', 'productType', 'heroMedia.variants']);

        $galleryMediaIds = MediaUsage::query()
            ->where('usable_type', $platformProduct->getMorphClass())
            ->where('usable_id', $platformProduct->id)
            ->where('field', 'gallery')
            ->orderBy('id')
            ->pluck('media_asset_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return view('dashboard.admin.platform-product-form', [
            'product' => $platformProduct,
            'galleryMediaIds' => $galleryMediaIds,
            'serviceCategories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'services' => ProductType::query()->with('serviceCategory')->orderBy('sort_order')->orderBy('name')->get(),
            'types' => PlatformProductType::cases(),
        ]);
    }

    public function update(Request $request, PlatformProduct $platformProduct): RedirectResponse
    {
        $data = $this->validated($request, $platformProduct->id);
        if (empty($data['slug'])) {
            $data['slug'] = $platformProduct->slug ?: Str::slug($data['title']);
        }
        $galleryIds = $data['gallery_media_ids'];
        unset($data['gallery_media_ids']);

        $platformProduct->update($data);
        $this->syncVariants($platformProduct, $request->input('variants', []), (float) $data['base_price']);
        $this->syncGallery($platformProduct, $galleryIds);
        $this->assertPublishable($platformProduct->fresh(['variants']), $data['status']);

        return redirect()->route('admin.platform-products')->with('status', 'Product updated.');
    }

    public function destroy(PlatformProduct $platformProduct): RedirectResponse
    {
        $this->mediaUsages->detachAllFor($platformProduct);
        $platformProduct->delete();

        return back()->with('status', 'Product deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('platform_products', 'slug')->ignore($ignoreId),
            ],
            'product_type_id' => ['required', 'exists:product_types,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['sometimes', 'boolean'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'hero_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', $this->mediaPaths->existsRule()],
            'demo_url' => ['nullable', 'url'],
            'demo_username' => ['nullable', 'string', 'max:255'],
            'demo_password' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:100'],
            'framework' => ['nullable', 'string', 'max:100'],
            'support_period' => ['nullable', 'string', 'max:100'],
            'support_text' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'provider' => ['nullable', 'string', 'max:80'],
            'provider_product_id' => ['nullable', 'string', 'max:255'],
            'provider_sku' => ['nullable', 'string', 'max:255'],
            'provider_meta_text' => ['nullable', 'string'],
            'fulfillment_mode' => ['required', Rule::in(['manual', 'auto_provision'])],
            'auto_renew' => ['sometimes', 'boolean'],
            'features_text' => ['nullable', 'string'],
            'requirements_text' => ['nullable', 'string'],
            'whats_included_text' => ['nullable', 'string'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.q' => ['nullable', 'string', 'max:500'],
            'faqs.*.a' => ['nullable', 'string'],
            'faqs.*.open' => ['nullable'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.duration_months' => ['nullable', 'integer', 'min:1'],
            'variants.*.is_default' => ['sometimes', 'boolean'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
        ]);

        $service = ProductType::query()->findOrFail($data['product_type_id']);

        $providerMeta = null;
        if (! empty($data['provider_meta_text'])) {
            $decoded = json_decode($data['provider_meta_text'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'provider_meta_text' => 'Provider meta must be valid JSON.',
                ]);
            }
            $providerMeta = $decoded;
        }

        $heroMediaId = isset($data['hero_media_id']) ? (int) $data['hero_media_id'] : null;
        $galleryIds = collect($data['gallery_media_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $payload = [
            'title' => $data['title'],
            'slug' => $data['slug'] ?? null,
            'product_type_id' => $service->id,
            'product_type' => $service->slug,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'base_price' => $data['base_price'],
            'hero_media_id' => $heroMediaId,
            'hero_image' => $this->mediaPaths->legacyPathFromMediaId($heroMediaId),
            'gallery_media_ids' => $galleryIds,
            'demo_url' => $data['demo_url'] ?? null,
            'demo_username' => $data['demo_username'] ?? null,
            'demo_password' => $data['demo_password'] ?? null,
            'industry' => $data['industry'] ?? null,
            'framework' => $data['framework'] ?? null,
            'support_period' => $data['support_period'] ?? null,
            'support_text' => $data['support_text'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_featured' => $request->boolean('is_featured'),
            'is_responsive' => $request->boolean('is_responsive', true),
            'is_seo_ready' => $request->boolean('is_seo_ready'),
            'provider' => $data['provider'] ?? 'manual',
            'provider_product_id' => $data['provider_product_id'] ?? null,
            'provider_sku' => $data['provider_sku'] ?? null,
            'provider_meta' => $providerMeta,
            'fulfillment_mode' => $data['fulfillment_mode'],
            'auto_renew' => $request->boolean('auto_renew'),
            'features' => FaqNormalizer::stringList($data['features_text'] ?? null),
            'requirements' => FaqNormalizer::stringList($data['requirements_text'] ?? null),
            'whats_included' => FaqNormalizer::stringList($data['whats_included_text'] ?? null),
            'faqs' => FaqNormalizer::fromRequest($data['faqs'] ?? null),
        ];

        if (Schema::hasColumn('platform_products', 'platform_category_id')) {
            $payload['platform_category_id'] = null;
        }

        return $payload;
    }

    private function assertPublishable(PlatformProduct $product, string $status): void
    {
        if ($status !== PlatformProductStatus::Published->value) {
            return;
        }

        $hasActive = $product->variants()->where('is_active', true)->exists();
        if (! $hasActive) {
            throw ValidationException::withMessages([
                'status' => 'Published products require at least one active variant.',
            ]);
        }
    }

    private function syncVariants(PlatformProduct $product, array $variants, float $fallbackPrice): void
    {
        $keptIds = [];
        $sort = 0;
        $hasDefault = false;

        foreach ($variants as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $price = isset($row['price']) && $row['price'] !== '' ? (float) $row['price'] : $fallbackPrice;
            $isDefault = ! empty($row['is_default']);
            if ($isDefault) {
                $hasDefault = true;
            }

            $attrs = [
                'platform_product_id' => $product->id,
                'name' => $name,
                'label' => $name,
                'duration_months' => $row['duration_months'] ?? null,
                'price' => $price,
                'sort_order' => $sort++,
                'is_default' => $isDefault,
                'is_active' => array_key_exists('is_active', $row) ? ! empty($row['is_active']) : true,
            ];

            if (! empty($row['id'])) {
                $variant = PlatformProductVariant::query()
                    ->where('platform_product_id', $product->id)
                    ->where('id', $row['id'])
                    ->first();
                if ($variant) {
                    $variant->update($attrs);
                    $keptIds[] = $variant->id;
                    continue;
                }
            }

            $variant = PlatformProductVariant::create($attrs + [
                'sku' => $product->slug.'-'.Str::random(4),
            ]);
            $keptIds[] = $variant->id;
        }

        if ($keptIds === []) {
            $variant = PlatformProductVariant::updateOrCreate(
                ['sku' => $product->slug.'-std'],
                [
                    'platform_product_id' => $product->id,
                    'name' => 'Standard',
                    'label' => 'Standard',
                    'price' => $fallbackPrice,
                    'is_default' => true,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
            $keptIds[] = $variant->id;
        } elseif (! $hasDefault) {
            PlatformProductVariant::where('id', $keptIds[0])->update(['is_default' => true]);
        }

        PlatformProductVariant::query()
            ->where('platform_product_id', $product->id)
            ->whereNotIn('id', $keptIds)
            ->delete();
    }

    /**
     * @param  list<int>  $galleryIds
     */
    private function syncGallery(PlatformProduct $product, array $galleryIds): void
    {
        $assets = MediaAsset::query()
            ->with('variants')
            ->whereIn('id', $galleryIds)
            ->get()
            ->keyBy('id');

        $product->images()->delete();
        foreach ($galleryIds as $i => $id) {
            $asset = $assets->get($id);
            if (! $asset) {
                continue;
            }

            PlatformProductImage::create([
                'platform_product_id' => $product->id,
                'media_asset_id' => $asset->id,
                'path' => $asset->variantStoragePath('medium') ?? $asset->legacyPublicPath('medium'),
                'alt' => $asset->alt ?: $product->title,
                'sort_order' => $i,
            ]);
        }

        $this->mediaUsages->syncUsages($product, [
            'hero' => $product->hero_media_id,
            'gallery' => $galleryIds === [] ? null : $galleryIds,
        ]);
    }
}
