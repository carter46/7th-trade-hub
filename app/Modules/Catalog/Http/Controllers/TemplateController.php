<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\PlatformCategory;
use App\Models\PlatformProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->integer('category') ?: null;
        $q = $request->string('q')->toString();

        $products = PlatformProduct::query()
            ->published()
            ->ofType(PlatformProductType::DocumentTemplate)
            ->with(['category', 'activeVariants'])
            ->when($categoryId, fn ($builder) => $builder->where('platform_category_id', $categoryId))
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('short_description', 'like', "%{$q}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $categories = PlatformCategory::query()
            ->where('product_type', PlatformProductType::DocumentTemplate->value)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('pages.templates', [
            'products' => $products,
            'categories' => $categories,
            'filters' => ['q' => $q, 'category' => $categoryId],
        ]);
    }

    public function show(string $slug): View
    {
        $product = PlatformProduct::query()
            ->published()
            ->ofType(PlatformProductType::DocumentTemplate)
            ->where('slug', $slug)
            ->with(['category', 'activeVariants', 'images'])
            ->firstOrFail();

        return view('pages.templates-show', [
            'product' => $product,
            'isFavorited' => auth()->check()
                && $product->favorites()->where('user_id', auth()->id())->exists(),
        ]);
    }
}
