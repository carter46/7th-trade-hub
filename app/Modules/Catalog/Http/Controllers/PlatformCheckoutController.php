<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\PlatformProduct;
use App\Modules\Catalog\Services\PlatformCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class PlatformCheckoutController extends Controller
{
    public function __construct(
        private PlatformCheckoutService $checkoutService
    ) {}

    public function show(string $slug): View|RedirectResponse
    {
        $product = PlatformProduct::query()
            ->published()
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

        return view('pages.checkout-platform', [
            'product' => $product,
            'variants' => $variants,
            'defaultVariantId' => $defaultVariant?->id,
            'basePrice' => (float) $product->base_price,
            'showDomainOptions' => in_array($product->product_type->value, $webTypes, true),
            'idempotencyKey' => (string) Str::uuid(),
            'walletBalance' => auth()->user()?->wallet?->balance,
        ]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $product = PlatformProduct::query()
            ->published()
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
            Log::error('Platform checkout failed', [
                'slug' => $slug,
                'user_id' => $request->user()?->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Checkout failed. Please try again or contact support.');
        }

        return redirect()
            ->route('dashboard.orders')
            ->with('success', 'Order '.$order->reference.' placed successfully.');
    }
}
