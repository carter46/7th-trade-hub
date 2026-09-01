<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PlatformProduct;
use App\Modules\Catalog\Services\CatalogBrowseService;
use App\Modules\Catalog\Services\CatalogContentResolver;
use App\Modules\Catalog\Services\PlatformCheckoutService;
use App\Services\Analytics\UserActivityRecorder;
use App\Services\Domains\DomainConnectionService;
use App\Services\Domains\DomainQuoteService;
use App\Support\Domains\DomainRegistrantContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        private DomainQuoteService $domainQuotes,
        private DomainConnectionService $domainConnections,
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
                ->with(['productType.serviceCategory', 'activeVariants', 'heroMedia'])
                ->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('short_description', 'like', "%{$q}%");
                })
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->paginate(12)
                ->withQueryString();
        }

        $wallet = $user->wallet ?? null;

        return view('dashboard.user.discover.services', compact(
            'groups',
            'searchResults',
            'q',
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
                    $category->load([
                        'services.cardMedia.variants',
                        'services.bannerMedia.variants',
                        'services.serviceCategory.cardMedia.variants',
                        'services.serviceCategory.bannerMedia.variants',
                    ]),
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
        if (in_array($slug, ['com-domain-registration', 'io-domain-registration', 'co-domain-registration', 'ng-domain-registration'], true)) {
            return redirect()->route('dashboard.services.product', config('domains.registration_product_slug', 'domain-registration'));
        }

        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $slug)
            ->with(['productType.serviceCategory', 'activeVariants', 'images', 'heroMedia.variants', 'siteIntegration'])
            ->firstOrFail();

        $typeSlug = $product->typeSlug();

        $this->activity->record($request->user()->id, 'viewed', $product, 'service.viewed');

        $groupSlug = $product->productType?->serviceCategory?->slug
            ?? $this->browse->groupForType((string) $typeSlug);

        $isDomainProduct = $product->product_type === PlatformProductType::Domain;
        $domainTldBundles = $isDomainProduct ? $this->domainTldBundlesForProduct($product) : ['featured' => [], 'advanced' => []];

        return view('dashboard.user.discover.services-product', [
            'product' => $product,
            'groupSlug' => $groupSlug,
            'groupLabel' => $groupSlug ? ($this->content->forGroup($groupSlug)['label'] ?? $groupSlug) : null,
            'wallet' => $request->user()->wallet,
            'isDomainProduct' => $isDomainProduct,
            'domainTlds' => $domainTldBundles['featured'],
            'domainTldsAdvanced' => $domainTldBundles['advanced'],
        ]);
    }

    public function domainTlds(): JsonResponse
    {
        $product = $this->domainQuotes->registrationProduct();
        $bundles = $this->domainTldBundlesForProduct($product);

        return response()->json([
            'tlds' => $bundles['featured'],
            'tlds_advanced' => $bundles['advanced'],
        ]);
    }

    public function domainQuote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_slug' => ['required', 'string', 'max:255'],
            'domain_label' => ['required', 'string', 'max:63'],
            'domain_tld' => ['required', 'string', 'max:63'],
        ]);

        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $data['product_slug'])
            ->firstOrFail();

        if ($product->product_type !== PlatformProductType::Domain
            && $product->product_type !== PlatformProductType::WebsitePackage) {
            abort(422, 'Invalid product for domain quote.');
        }

        $quoteProduct = $product->product_type === PlatformProductType::Domain
            ? $product
            : $this->domainQuotes->registrationProduct();

        $result = $this->domainQuotes->quoteForUser(
            $request->user(),
            $quoteProduct,
            $data['domain_label'],
            $data['domain_tld'],
        );

        return response()->json($result);
    }

    public function domainConnectScan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'domain_fqdn' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->domainConnections->scanForUser($request->user(), $data['domain_fqdn']);

        $status = 200;
        if (($result['status'] ?? '') === 'invalid' || ! empty($result['message'])) {
            if (! ($result['registered'] ?? false) || ($result['already_connected'] ?? false)) {
                $status = 200;
            }
        }

        return response()->json($result, $status);
    }

    public function checkout(Request $request, string $slug): View|RedirectResponse
    {
        if (in_array($slug, ['com-domain-registration', 'io-domain-registration', 'co-domain-registration', 'ng-domain-registration'], true)) {
            return redirect()->route('dashboard.services.checkout', config('domains.registration_product_slug', 'domain-registration'));
        }

        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $slug)
            ->with('activeVariants')
            ->firstOrFail();

        if ($product->product_type === PlatformProductType::WebsitePackage && ! $request->filled('variant')) {
            return redirect()
                ->route('dashboard.services.product', $product->slug)
                ->with('error', 'Choose a plan before checkout.');
        }

        $variants = $product->activeVariants->sortBy('price')->values();
        $requestedVariantId = $request->integer('variant') ?: null;
        $defaultVariant = $requestedVariantId
            ? ($variants->firstWhere('id', $requestedVariantId) ?? $variants->first())
            : $variants->first();

        if ($requestedVariantId && (int) $defaultVariant?->id !== $requestedVariantId) {
            return redirect()
                ->route('dashboard.services.product', $product->slug)
                ->with('error', 'Selected plan is unavailable.');
        }

        $isWebsitePackage = $product->product_type === PlatformProductType::WebsitePackage;
        $isDomainProduct = $product->product_type === PlatformProductType::Domain;
        $showPlanSummary = $requestedVariantId !== null || $isDomainProduct;

        $this->activity->record($request->user()->id, 'viewed', $product, 'service.checkout');

        $renewTool = null;
        if ($request->filled('renew')) {
            $renewTool = \App\Models\UserTool::query()
                ->where('public_id', $request->string('renew')->toString())
                ->where('user_id', $request->user()->id)
                ->where('platform_product_id', $product->id)
                ->first();
        }

        if ($isDomainProduct && ! $request->filled('quote_token')) {
            return redirect()
                ->route('dashboard.services.product', $product->slug)
                ->with('error', 'Check domain availability before checkout.');
        }

        $domainTldBundles = ($isWebsitePackage || $isDomainProduct)
            ? $this->domainTldBundlesForProduct($product)
            : ['featured' => [], 'advanced' => []];

        return view('dashboard.user.discover.services-checkout', [
            'product' => $product,
            'variants' => $variants,
            'defaultVariantId' => $defaultVariant?->id,
            'basePrice' => (float) $product->displayPrice(),
            'showPlanSummary' => $showPlanSummary,
            'isWebsitePackage' => $isWebsitePackage,
            'isDomainProduct' => $isDomainProduct,
            'requireDomainChoice' => $isWebsitePackage,
            'domainTlds' => $domainTldBundles['featured'],
            'domainTldsAdvanced' => $domainTldBundles['advanced'],
            'quoteToken' => $request->string('quote_token')->toString() ?: null,
            'quotedFqdn' => $request->string('domain_fqdn')->toString() ?: null,
            'quotedPrice' => $request->string('quoted_price')->toString() ?: null,
            'idempotencyKey' => (string) Str::uuid(),
            'wallet' => $request->user()->wallet,
            'renewTool' => $renewTool,
            'gatewayEnabled' => $this->checkoutService->gatewayEnabled(),
            'manualBankTransferEnabled' => $this->checkoutService->manualBankTransferEnabledForCheckout(),
        ]);
    }

    public function purchase(Request $request, string $slug): RedirectResponse
    {
        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $slug)
            ->firstOrFail();

        $gatewayEnabled = $this->checkoutService->gatewayEnabled();
        $manualBankEnabled = $this->checkoutService->manualBankTransferEnabledForCheckout();
        $hasWallet = (bool) $request->user()->wallet;

        $allowedMethods = [];
        if ($hasWallet) {
            $allowedMethods[] = 'wallet';
        }
        if ($gatewayEnabled) {
            $allowedMethods[] = 'gateway';
        }
        if ($manualBankEnabled) {
            $allowedMethods[] = Order::PAYMENT_MANUAL_BANK_TRANSFER;
        }

        if ($allowedMethods === []) {
            return back()->withInput()->with('error', 'No payment method is available. Create a wallet, enable card/transfer checkout, or ask support about bank transfer for orders.');
        }

        $rules = [
            'variant_id' => ['nullable', 'integer', 'exists:platform_product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'domain_mode' => ['nullable', 'in:buy,connect'],
            'domain_label' => ['nullable', 'string', 'max:63'],
            'domain_tld' => ['nullable', 'string', 'max:63'],
            'domain_quote_token' => ['nullable', 'string', 'max:128'],
            'domain_fqdn' => ['nullable', 'string', 'max:255'],
            'domain_name' => ['nullable', 'string', 'max:255'],
            'domain_connect_acknowledged' => ['nullable', 'boolean'],
            'idempotency_key' => ['required', 'string', 'uuid', 'max:64'],
            'renew_user_tool_id' => ['nullable', 'integer', 'exists:user_tools,id'],
            'payment_method' => ['nullable', 'in:'.implode(',', $allowedMethods)],
        ];

        if ($this->purchaseRequiresRegistrant($product, $request)) {
            $rules = array_merge($rules, DomainRegistrantContact::validationRules());
        }

        $data = $request->validate($rules);

        $data['payment_method'] = $data['payment_method']
            ?? ($hasWallet ? 'wallet' : ($gatewayEnabled ? 'gateway' : ($manualBankEnabled ? Order::PAYMENT_MANUAL_BANK_TRANSFER : null)));

        if (! $data['payment_method'] || ! in_array($data['payment_method'], $allowedMethods, true)) {
            return back()->withInput()->with('error', 'Choose a valid payment method.');
        }

        if ($product->product_type === PlatformProductType::WebsitePackage) {
            $data['quantity'] = 1;
            if ((int) $request->input('quantity', 1) !== 1) {
                return back()->withInput()->with('error', 'Website packages must be purchased with quantity 1.');
            }
            if (empty($data['variant_id'])) {
                return back()->withInput()->with('error', 'Choose a plan before checkout.');
            }
        }

        if (($data['payment_method'] ?? '') === 'gateway') {
            $data['redirect_url'] = route('dashboard.services.payment-callback', ['slug' => $product->slug]);
        }

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

        if ($order->payment_method === 'gateway' && in_array($order->status, ['pending', 'processing'], true)) {
            if (! filled($order->checkout_url)) {
                return back()->withInput()->with('error', 'Unable to start payment gateway checkout.');
            }

            return redirect()->away($order->checkout_url);
        }

        if ($order->payment_method === Order::PAYMENT_MANUAL_BANK_TRANSFER && $order->status === 'pending') {
            return redirect()
                ->route('dashboard.orders.manual-payment', $order)
                ->with('status', 'Order '.$order->reference.' created. Complete your bank transfer using the instructions below.');
        }

        if (! empty($data['renew_user_tool_id'])) {
            $tool = \App\Models\UserTool::query()->find($data['renew_user_tool_id']);

            return redirect()
                ->route('dashboard.my-tools.show', $tool)
                ->with('success', 'Subscription renewed. Order '.$order->reference.'.');
        }

        $tool = \App\Models\UserTool::query()
            ->where('order_id', $order->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($tool) {
            return redirect()
                ->route('dashboard.my-tools.show', $tool)
                ->with('success', 'Order '.$order->reference.' placed. Your tool is pending setup.');
        }

        return redirect()
            ->route('dashboard.service-orders')
            ->with('success', 'Order '.$order->reference.' placed successfully.');
    }

    public function paymentCallback(Request $request, string $slug): RedirectResponse
    {
        $paymentReference = $request->string('paymentReference')->toString()
            ?: $request->string('payment_reference')->toString();

        if ($paymentReference === '') {
            return redirect()
                ->route('dashboard.services.checkout', $slug)
                ->with('error', 'Payment reference missing. If you paid, wait a moment and check My Tools / Service orders.');
        }

        $order = \App\Models\Order::query()
            ->where('provider_payment_reference', $paymentReference)
            ->where('user_id', $request->user()->id)
            ->where('source', 'platform')
            ->first();

        if (! $order) {
            return redirect()
                ->route('dashboard.services.checkout', $slug)
                ->with('error', 'Order not found for this payment.');
        }

        try {
            $rail = app(\App\Modules\Wallet\Payments\Contracts\PaymentRailInterface::class);
            $verified = $rail->verifyTransaction($paymentReference);
            $status = strtoupper((string) ($verified['paymentStatus'] ?? ''));
            $amountPaid = (string) ($verified['amountPaid'] ?? '0');

            if (in_array($status, ['PAID', 'SUCCESS', 'COMPLETED'], true)
                && bccomp($amountPaid, (string) $order->total_amount, 2) === 0) {
                $order = $this->checkoutService->fulfillPaidGatewayOrder($order);
            }
        } catch (\Throwable $e) {
            Log::warning('Platform gateway callback verify failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }

        $order->refresh();

        if ($order->status !== 'paid') {
            return redirect()
                ->route('dashboard.services.checkout', $slug)
                ->with('error', 'Payment is still pending. If you completed payment, refresh shortly or check Service orders.');
        }

        $tool = \App\Models\UserTool::query()
            ->where('order_id', $order->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($tool) {
            return redirect()
                ->route('dashboard.my-tools.show', $tool)
                ->with('success', 'Payment confirmed. Order '.$order->reference.'.');
        }

        return redirect()
            ->route('dashboard.service-orders')
            ->with('success', 'Payment confirmed. Order '.$order->reference.'.');
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
            ->with(['productType.serviceCategory', 'activeVariants', 'heroMedia.variants'])
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

    private function purchaseRequiresRegistrant(PlatformProduct $product, Request $request): bool
    {
        if ($product->product_type === PlatformProductType::Domain) {
            return true;
        }

        if ($product->product_type === PlatformProductType::WebsitePackage) {
            return $request->input('domain_mode') === 'buy';
        }

        return false;
    }

    /**
     * @return array{featured: list<array{tld: string, label: string}>, advanced: list<array{tld: string, label: string}>}
     */
    private function domainTldBundlesForProduct(PlatformProduct $product): array
    {
        $registrationProduct = $product->product_type === PlatformProductType::Domain
            ? $product
            : $this->domainQuotes->registrationProduct();

        return [
            'featured' => $this->domainQuotes->featuredTldOptionsForUi($registrationProduct),
            'advanced' => $this->domainQuotes->advancedTldOptionsForUi($registrationProduct),
        ];
    }
}
