<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\PlatformProduct;
use App\Modules\Catalog\Services\CatalogBrowseService;
use App\Modules\Catalog\Services\CatalogContentResolver;
use App\Modules\Catalog\Services\PlatformCheckoutService;
use App\Services\Analytics\UserActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class DiscoverServicesController extends Controller
{
    public function __construct(
        private CatalogBrowseService $browse,
        private CatalogContentResolver $content,
        private UserActivityRecorder $activity,
        private PlatformCheckoutService $checkoutService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->activity->record($user->id, 'viewed', null, 'services.hub');

        $q = $request->string('q')->toString();
        $groups = $this->dashboardGroupCards();
        $types = $this->browse->allGroupTypeValues();

        $searchResults = null;
        if ($q !== '') {
            $searchResults = PlatformProduct::query()
                ->visibleToPublic()
                ->ofTypeMany($types)
                ->with(['productType.serviceCategory', 'activeVariants'])
                ->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('short_description', 'like', "%{$q}%");
                })
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->paginate(12)
                ->withQueryString();
        }

        $purchasedProductIds = collect();
        if (Schema::hasTable('order_items')) {
            $purchasedProductIds = OrderItem::query()
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->whereIn('status', ['paid', 'completed', 'processing', 'delivered']);
                })
                ->where(function ($q) {
                    $q->where('item_type', 'platform_product')
                        ->orWhereNotNull('platform_product_variant_id');
                })
                ->with('variant:id,platform_product_id')
                ->orderByDesc('id')
                ->limit(20)
                ->get()
                ->map(fn (OrderItem $item) => $item->item_type === 'platform_product'
                    ? $item->item_id
                    : $item->variant?->platform_product_id)
                ->filter()
                ->unique();
        }

        $recentlyPurchased = $purchasedProductIds->isNotEmpty()
            ? PlatformProduct::query()->visibleToPublic()->whereIn('id', $purchasedProductIds)->limit(6)->get()
            : collect();

        $wallet = $user->wallet ?? null;

        return view('dashboard.user.discover.services', compact(
            'groups',
            'searchResults',
            'q',
            'recentlyPurchased',
            'wallet',
        ));
    }

    public function browse(Request $request, string $segment): View|RedirectResponse
    {
        $user = $request->user();

        if ($this->browse->isGroup($segment)) {
            $category = $this->browse->usesDbHierarchy()
                ? $this->browse->findServiceCategory($segment)
                : null;

            if ($category?->isMarketplaceLink()) {
                return redirect()->route('dashboard.marketplace');
            }

            $resolved = $this->content->forGroup($segment);
            $typeKeys = $resolved['types'] ?? config('catalog.groups.'.$segment.'.types', []);
            if ($category) {
                $typeKeys = $category->services()->active()->orderBy('sort_order')->pluck('slug')->all();
            }

            $typeFilter = $request->string('type')->toString();
            if ($typeFilter !== '' && in_array($typeFilter, $typeKeys, true)) {
                return $this->browseType($request, $typeFilter, $segment, $resolved);
            }

            if ($category && $typeFilter === '' && $request->string('q')->toString() === '') {
                $typeCards = $this->browse->serviceCardsForCategory(
                    $category->load(['services.cardMedia.variants', 'services.bannerMedia.variants']),
                    $this->content,
                )
                    ->map(function (array $card) use ($segment) {
                        $card['href'] = route('dashboard.services.browse', [
                            'segment' => $segment,
                            'type' => $card['slug'],
                        ]);

                        return $card;
                    });

                $this->activity->record($user->id, 'viewed', null, 'services.browse.'.$segment);

                return view('dashboard.user.discover.services-browse', [
                    'segment' => $segment,
                    'title' => $resolved['label'] ?? $segment,
                    'subtitle' => $resolved['short_description'] ?? null,
                    'typeCards' => $typeCards,
                    'products' => null,
                    'filters' => ['q' => '', 'type' => null],
                    'typeKeys' => $typeKeys,
                    'wallet' => $user->wallet,
                ]);
            }

            return $this->browseProducts($request, $typeKeys, $segment, $resolved, $typeFilter !== '' ? $typeFilter : null);
        }

        if ($this->browse->isType($segment)) {
            $groupSlug = $this->browse->groupForType($segment);

            return $this->browseType($request, $segment, $groupSlug, $this->content->forType($segment));
        }

        abort(404);
    }

    public function product(Request $request, string $slug): View|RedirectResponse
    {
        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $slug)
            ->with(['productType.serviceCategory', 'activeVariants', 'images', 'heroMedia.variants'])
            ->firstOrFail();

        $typeSlug = $product->typeSlug();
        try {
            $enumType = PlatformProductType::from((string) $typeSlug);
        } catch (\ValueError) {
            $enumType = null;
        }

        if ($enumType === PlatformProductType::DocumentTemplate) {
            return redirect()->route('templates.show', $product->slug);
        }
        if (in_array($enumType, [PlatformProductType::WebsitePackage, PlatformProductType::WebsiteTemplate], true)) {
            return redirect()->route('website-listings.show', $product->slug);
        }

        $this->activity->record($request->user()->id, 'viewed', $product, 'service.viewed');

        $groupSlug = $product->productType?->serviceCategory?->slug
            ?? $this->browse->groupForType((string) $typeSlug);

        return view('dashboard.user.discover.services-product', [
            'product' => $product,
            'groupSlug' => $groupSlug,
            'groupLabel' => $groupSlug ? ($this->content->forGroup($groupSlug)['label'] ?? $groupSlug) : null,
            'wallet' => $request->user()->wallet,
        ]);
    }

    public function checkout(Request $request, string $slug): View
    {
        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $slug)
            ->with('activeVariants')
            ->firstOrFail();

        $variants = $product->activeVariants;
        $defaultVariant = $variants->firstWhere('is_default', true) ?? $variants->first();
        $webTypes = [
            PlatformProductType::WebsitePackage->value,
            PlatformProductType::WebsiteTemplate->value,
            PlatformProductType::Domain->value,
        ];

        $this->activity->record($request->user()->id, 'viewed', $product, 'service.checkout');

        return view('dashboard.user.discover.services-checkout', [
            'product' => $product,
            'variants' => $variants,
            'defaultVariantId' => $defaultVariant?->id,
            'basePrice' => (float) $product->base_price,
            'showDomainOptions' => in_array(
                $product->product_type instanceof \BackedEnum
                    ? $product->product_type->value
                    : (string) $product->product_type,
                $webTypes,
                true
            ),
            'idempotencyKey' => (string) Str::uuid(),
            'wallet' => $request->user()->wallet,
        ]);
    }

    public function purchase(Request $request, string $slug): RedirectResponse
    {
        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $slug)
            ->firstOrFail();

        $data = $request->validate([
            'variant_id' => ['nullable', 'integer', 'exists:platform_product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'domain_mode' => ['nullable', 'in:none,buy,connect'],
            'domain_name' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'uuid', 'max:64'],
        ]);

        try {
            $order = $this->checkoutService->purchase($request->user(), $product, $data);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Dashboard platform checkout failed', [
                'slug' => $slug,
                'user_id' => $request->user()?->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Checkout failed. Please try again or contact support.');
        }

        return redirect()
            ->route('dashboard.service-orders')
            ->with('success', 'Order '.$order->reference.' placed successfully.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function dashboardGroupCards()
    {
        return $this->browse->groupCards($this->content)->map(function (array $group) {
            $slug = $group['slug'] ?? null;
            if (! $slug) {
                return $group;
            }

            $isMarketplace = ($group['mode'] ?? null) === 'marketplace_link'
                || str_contains((string) ($group['href'] ?? ''), 'marketplace');

            $group['href'] = $isMarketplace
                ? route('dashboard.marketplace')
                : route('dashboard.services.browse', $slug);

            return $group;
        });
    }

    /**
     * @param  list<string>  $typeKeys
     * @param  array<string, mixed>  $resolved
     */
    private function browseType(Request $request, string $type, ?string $groupSlug, array $resolved): View
    {
        return $this->browseProducts($request, [$type], $groupSlug ?? $type, $resolved, null);
    }

    /**
     * @param  list<string>  $typeKeys
     * @param  array<string, mixed>  $resolved
     */
    private function browseProducts(
        Request $request,
        array $typeKeys,
        string $segment,
        array $resolved,
        ?string $typeFilter,
    ): View {
        $q = $request->string('q')->toString();
        $activeTypes = $typeFilter ? [$typeFilter] : $typeKeys;

        $products = PlatformProduct::query()
            ->visibleToPublic()
            ->ofTypeMany($activeTypes)
            ->with(['productType.serviceCategory', 'activeVariants'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('short_description', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $this->activity->record($request->user()->id, 'viewed', null, 'services.browse.'.$segment);

        return view('dashboard.user.discover.services-browse', [
            'segment' => $segment,
            'title' => $resolved['label'] ?? $segment,
            'subtitle' => $resolved['short_description'] ?? null,
            'typeCards' => null,
            'products' => $products,
            'filters' => ['q' => $q, 'type' => $typeFilter],
            'typeKeys' => $typeKeys,
            'wallet' => $request->user()->wallet,
        ]);
    }
}
