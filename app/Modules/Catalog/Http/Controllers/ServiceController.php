<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\PlatformCategory;
use App\Models\PlatformProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /** @return list<string> */
    private function serviceTypeValues(): array
    {
        return collect(config('catalog.divisions'))
            ->flatMap(fn ($division) => $division['types'] ?? [])
            ->unique()
            ->values()
            ->all();
    }

    public function index(Request $request): View
    {
        $validTypes = $this->serviceTypeValues();

        $type = $request->string('type')->toString() ?: null;
        if ($type && ! in_array($type, $validTypes, true)) {
            $type = null;
        }
        $categoryId = $request->integer('category') ?: null;
        $division = $request->string('division')->toString() ?: null;
        if ($division && ! isset(config('catalog.divisions')[$division])) {
            $division = null;
        }
        $q = $request->string('q')->toString();

        $divisionTypes = null;
        if ($division && isset(config('catalog.divisions')[$division])) {
            $divisionTypes = config('catalog.divisions.'.$division.'.types');
        }

        $query = PlatformProduct::query()
            ->published()
            ->whereIn('product_type', $validTypes)
            ->with(['category', 'activeVariants'])
            ->when($divisionTypes, fn ($builder) => $builder->whereIn('product_type', $divisionTypes))
            ->when($type, fn ($builder) => $builder->ofType($type))
            ->when($categoryId, fn ($builder) => $builder->where('platform_category_id', $categoryId))
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('short_description', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title');

        $products = $query->paginate(12)->withQueryString();

        $featuredQuery = PlatformProduct::published()
            ->featured()
            ->whereIn('product_type', $divisionTypes ?: $validTypes)
            ->with('activeVariants')
            ->orderBy('sort_order')
            ->limit(6);

        $featured = $featuredQuery->get();

        $categories = PlatformCategory::query()
            ->where('is_active', true)
            ->when($divisionTypes, fn ($builder) => $builder->whereIn('product_type', $divisionTypes))
            ->when($type, fn ($builder) => $builder->where('product_type', $type))
            ->when(! $divisionTypes && ! $type, fn ($builder) => $builder->whereIn('product_type', $validTypes))
            ->orderBy('sort_order')
            ->get();

        $typeCases = collect(PlatformProductType::cases())
            ->filter(fn (PlatformProductType $case) => in_array($case->value, $validTypes, true))
            ->values();

        return view('pages.services', [
            'products' => $products,
            'featured' => $featured,
            'categories' => $categories,
            'divisions' => config('catalog.divisions'),
            'types' => $typeCases,
            'filters' => [
                'q' => $q,
                'type' => $type,
                'category' => $categoryId,
                'division' => $division,
            ],
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $product = PlatformProduct::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'images', 'activeVariants'])
            ->firstOrFail();

        return match ($product->product_type) {
            PlatformProductType::DocumentTemplate => redirect()->route('templates.show', $product->slug),
            PlatformProductType::WebsitePackage,
            PlatformProductType::WebsiteTemplate => redirect()->route('website-listings.show', $product->slug),
            default => view('pages.services-show', [
                'product' => $product,
                'isFavorited' => $this->isFavorited($product),
            ]),
        };
    }

    private function isFavorited(PlatformProduct $product): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $product->favorites()->where('user_id', $user->id)->exists();
    }
}
