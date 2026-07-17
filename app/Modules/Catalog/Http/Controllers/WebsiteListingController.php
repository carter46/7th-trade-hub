<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\PlatformCategory;
use App\Models\PlatformProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteListingController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->integer('category') ?: null;
        $industry = $request->string('industry')->toString() ?: null;
        $framework = $request->string('framework')->toString() ?: null;
        $q = $request->string('q')->toString();

        $products = PlatformProduct::query()
            ->published()
            ->whereIn('product_type', [
                PlatformProductType::WebsitePackage->value,
                PlatformProductType::WebsiteTemplate->value,
            ])
            ->with(['category', 'activeVariants', 'images'])
            ->when($categoryId, fn ($builder) => $builder->where('platform_category_id', $categoryId))
            ->when($industry, fn ($builder) => $builder->where('industry', $industry))
            ->when($framework, fn ($builder) => $builder->where('framework', $framework))
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('short_description', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        $categories = PlatformCategory::query()
            ->whereIn('product_type', [
                PlatformProductType::WebsitePackage->value,
                PlatformProductType::WebsiteTemplate->value,
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('pages.website-listings', [
            'products' => $products,
            'categories' => $categories,
            'filters' => compact('q', 'categoryId', 'industry', 'framework') + [
                'category' => $categoryId,
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $product = PlatformProduct::query()
            ->published()
            ->whereIn('product_type', [
                PlatformProductType::WebsitePackage->value,
                PlatformProductType::WebsiteTemplate->value,
            ])
            ->where('slug', $slug)
            ->with(['category', 'images', 'activeVariants'])
            ->firstOrFail();

        return view('pages.website-listings-show', [
            'product' => $product,
            'isFavorited' => auth()->check()
                && $product->favorites()->where('user_id', auth()->id())->exists(),
        ]);
    }
}
