<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PlatformProduct;
use App\Modules\Catalog\Services\PlatformCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PlatformCheckoutController extends Controller
{
    public function __construct(
        private PlatformCheckoutService $checkoutService
    ) {}

    public function show(string $slug): RedirectResponse
    {
        // Purchase always happens in the authenticated dashboard flow.
        return redirect()->route('dashboard.services.checkout', $slug);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        // Legacy public POST — keep fulfilling, then send users to service orders.
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
            return redirect()
                ->route('dashboard.services.checkout', $slug)
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Platform checkout failed', [
                'slug' => $slug,
                'user_id' => $request->user()?->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('dashboard.services.checkout', $slug)
                ->withInput()
                ->with('error', 'Checkout failed. Please try again or contact support.');
        }

        return redirect()
            ->route('dashboard.service-orders')
            ->with('success', 'Order '.$order->reference.' placed successfully.');
    }
}
