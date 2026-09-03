<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\PlatformProduct;
use App\Models\PlatformProductVariant;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use App\Services\Domains\DomainQuoteService;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use App\Support\Domains\DomainProductTldPolicy;
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
        private DomainQuoteService $domainQuotes,
    ) {}

    public function index(Request $request): View
    {
        $products = PlatformProduct::query()
            ->with(['productType.serviceCategory', 'heroMedia.variants', 'activeVariants'])
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

        $siblings = PlatformProduct::query()
            ->whereHas('productType.serviceCategory', fn ($q) => $q->system());
        $siblingMax = max(1, (clone $siblings)->count());

        return view('dashboard.admin.platform-product-form', [
            'product' => $platformProduct,
            'lockedCatalog' => true,
            'siblingMax' => $siblingMax,
            'domainFloorExample' => $platformProduct->product_type === PlatformProductType::Domain
                ? $this->domainQuotes->pricingFloorExample($platformProduct)
                : null,
            'registryTlds' => $platformProduct->product_type === PlatformProductType::Domain
                ? $this->domainQuotes->registryTldOptionsForUi()
                : [],
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

        $siblings = PlatformProduct::query()
            ->whereHas('productType.serviceCategory', fn ($q) => $q->system());
        $siblingMax = max(1, (clone $siblings)->count());

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in([
                PlatformProductStatus::Draft->value,
                PlatformProductStatus::Published->value,
            ])],
            'sort_order' => ['required', 'integer', 'min:1', 'max:'.$siblingMax],
            'hero_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['required', 'integer'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.description' => ['nullable', 'string', 'max:2000'],
            'domain_markup_percent' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'domain_usd_ngn_rate' => ['nullable', 'numeric', 'min:0'],
            'allowed_tlds' => ['nullable', 'array', 'min:1'],
            'allowed_tlds.*' => ['string', 'max:63'],
            'tutorial_url' => ['nullable', 'string', 'max:500'],
            'tutorial_description' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($platformProduct->product_type !== PlatformProductType::Domain) {
            $rawTutorial = trim((string) ($data['tutorial_url'] ?? ''));
            if ($rawTutorial !== '') {
                $normalizedTutorial = preg_match('#^https?://#i', $rawTutorial)
                    ? $rawTutorial
                    : 'https://'.$rawTutorial;
                if (! filter_var($normalizedTutorial, FILTER_VALIDATE_URL)) {
                    throw ValidationException::withMessages([
                        'tutorial_url' => 'Enter a valid tutorial video URL.',
                    ]);
                }
                $data['tutorial_url'] = $normalizedTutorial;
            }
        }

        $heroMediaId = filled($data['hero_media_id'] ?? null) ? (int) $data['hero_media_id'] : null;
        $heroPath = $this->mediaPaths->legacyPathFromMediaId($heroMediaId);

        $updatePayload = [
            'title' => $data['title'],
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'is_featured' => $request->boolean('is_featured'),
            'hero_media_id' => $heroMediaId,
            'hero_image' => $heroPath,
        ];

        if ($platformProduct->product_type !== PlatformProductType::Domain) {
            $tutorialUrl = trim((string) ($data['tutorial_url'] ?? ''));
            if ($tutorialUrl !== '' && ! preg_match('#^https?://#i', $tutorialUrl)) {
                $tutorialUrl = 'https://'.$tutorialUrl;
            }
            $updatePayload['tutorial_url'] = $tutorialUrl !== '' ? $tutorialUrl : null;
            $updatePayload['tutorial_description'] = filled($data['tutorial_description'] ?? null)
                ? trim((string) $data['tutorial_description'])
                : null;
        }

        if ($platformProduct->product_type === PlatformProductType::Domain) {
            $meta = $platformProduct->meta ?? [];
            if ($request->has('domain_markup_percent')) {
                $meta['domain_markup_percent'] = (float) ($data['domain_markup_percent'] ?? 0);
            }
            if ($request->has('domain_usd_ngn_rate')) {
                $meta['domain_fx_policy'] = array_merge($meta['domain_fx_policy'] ?? [], [
                    'usd_ngn_rate' => (float) ($data['domain_usd_ngn_rate'] ?? 0),
                ]);
            }
            $allowed = DomainProductTldPolicy::normalizeList($data['allowed_tlds'] ?? []);
            if ($allowed === []) {
                throw ValidationException::withMessages([
                    'allowed_tlds' => 'Select at least one allowed extension.',
                ]);
            }
            $meta['allowed_tlds'] = $allowed;
            $updatePayload['meta'] = $meta;
        }

        $platformProduct->update($updatePayload);

        SortOrder::move($platformProduct, (int) $data['sort_order'], $siblings);

        if ($platformProduct->product_type !== PlatformProductType::Domain) {
            $this->updateExistingVariants($platformProduct, $data['variants'] ?? []);
        }
        $this->mediaUsages->syncUsages($platformProduct, [
            'hero' => $heroMediaId,
        ]);

        if ($data['status'] === PlatformProductStatus::Published->value) {
            $this->assertPublishable($platformProduct->fresh(['variants']));
        }

        if ($platformProduct->product_type === PlatformProductType::Domain) {
            app(\App\Services\Domains\DomainCacheInvalidator::class)->invalidateAllDomainPricingCaches();
        }

        return redirect()
            ->route('admin.platform-products.edit', $platformProduct)
            ->with('status', 'Product updated.');
    }

    public function toggle(PlatformProduct $platformProduct): RedirectResponse
    {
        $platformProduct->loadMissing('productType.serviceCategory');
        if (! $platformProduct->productType?->serviceCategory?->isSystem()) {
            return back()->with('error', __('That product is not under a fixed platform category.'));
        }

        if ($platformProduct->status === PlatformProductStatus::Published) {
            $platformProduct->update(['status' => PlatformProductStatus::Draft]);
            $message = 'Product deactivated.';
        } else {
            $this->assertPublishable($platformProduct->fresh(['variants']));
            $platformProduct->update(['status' => PlatformProductStatus::Published]);
            $message = 'Product activated.';
        }

        return back()->with('status', $message);
    }

    public function destroy(): RedirectResponse
    {
        return redirect()
            ->route('admin.platform-products')
            ->with('error', __('Platform products cannot be deleted. Deactivate them instead.'));
    }

    /**
     * @param  list<array{id: int, price: mixed, description?: string|null}>  $variants
     */
    private function updateExistingVariants(PlatformProduct $product, array $variants): void
    {
        if ($variants === []) {
            return;
        }

        $existingIds = $product->variants()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $prices = [];

        foreach ($variants as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0 || ! in_array($id, $existingIds, true)) {
                throw ValidationException::withMessages([
                    'variants' => 'Variant structure is fixed. You can only change prices and descriptions of existing variants.',
                ]);
            }

            $price = (float) $row['price'];
            $prices[] = $price;

            $payload = ['price' => $price];
            if (array_key_exists('description', $row)) {
                $payload['description'] = trim((string) ($row['description'] ?? '')) ?: null;
            }

            PlatformProductVariant::query()
                ->where('id', $id)
                ->where('platform_product_id', $product->id)
                ->update($payload);
        }

        if ($prices !== []) {
            $product->update(['base_price' => min($prices)]);
        }
    }

    private function assertPublishable(PlatformProduct $product): void
    {
        if ($product->product_type === PlatformProductType::Domain) {
            $meta = $product->meta ?? [];
            $rate = (float) ($meta['domain_fx_policy']['usd_ngn_rate'] ?? 0);
            if ($rate <= 0) {
                throw ValidationException::withMessages([
                    'domain_usd_ngn_rate' => 'Set a USD → NGN rate before publishing the domain product.',
                ]);
            }

            $floor = $this->domainQuotes->pricingFloorExample($product);
            if ($floor !== null) {
                $markup = max(0, (float) ($meta['domain_markup_percent'] ?? 0));
                $ngnCost = $floor['provider_cost'];
                if (strtoupper($floor['provider_currency']) === 'USD') {
                    $ngnCost = $floor['provider_cost'] * $rate;
                }
                $minRetail = ceil($ngnCost);
                if ((float) $floor['retail_ngn'] < $minRetail) {
                    throw ValidationException::withMessages([
                        'domain_markup_percent' => 'Markup and FX settings would price domains below provider cost. Increase markup or FX rate.',
                    ]);
                }
                if ($markup < 0) {
                    throw ValidationException::withMessages([
                        'domain_markup_percent' => 'Markup cannot be negative.',
                    ]);
                }
            }

            return;
        }

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
