<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\PlatformProduct;
use App\Modules\Catalog\Services\CatalogBrowseService;
use Illuminate\Http\RedirectResponse;

class TemplateController extends Controller
{
    /** @deprecated Legacy /templates URL — redirects to Receipt service. */
    public function index(): RedirectResponse
    {
        return redirect()->route('services.type', [
            'category' => 'business-documents',
            'service' => 'receipt',
        ], 301);
    }

    /** @deprecated Legacy /templates/{slug} — redirects to canonical product URL. */
    public function show(string $slug): RedirectResponse
    {
        $product = PlatformProduct::query()
            ->visibleToPublic()
            ->where('slug', $slug)
            ->ofTypeMany([PlatformProductType::Receipt->value, PlatformProductType::Document->value])
            ->with(['productType.serviceCategory'])
            ->firstOrFail();

        return redirect()->to(app(CatalogBrowseService::class)->productUrl($product), 301);
    }
}
