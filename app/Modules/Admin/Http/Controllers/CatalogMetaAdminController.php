<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ExchangeRate;
use App\Models\PlatformCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CatalogMetaAdminController extends Controller
{
    public function marketplaceCategories(): View
    {
        $parents = Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return view('dashboard.admin.marketplace-categories', compact('parents'));
    }

    public function storeMarketplaceCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'parent_id' => $data['parent_id'] ?? null,
            'type' => 'marketplace',
            'is_active' => true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', 'Category created.');
    }

    public function platformCategories(): View
    {
        $categories = PlatformCategory::orderBy('product_type')->orderBy('sort_order')->get();

        return view('dashboard.admin.platform-categories', compact('categories'));
    }

    public function storePlatformCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        PlatformCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'product_type' => $data['product_type'],
            'is_active' => true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', 'Platform category created.');
    }

    public function togglePlatformCategory(PlatformCategory $platformCategory): RedirectResponse
    {
        $platformCategory->update(['is_active' => ! $platformCategory->is_active]);

        return back()->with('status', 'Category '.($platformCategory->is_active ? 'activated' : 'deactivated').'.');
    }

    public function toggleMarketplaceCategory(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('status', 'Category '.($category->is_active ? 'activated' : 'deactivated').'.');
    }

    public function exchangeRates(): View
    {
        return view('dashboard.admin.exchange-rates', [
            'rates' => ExchangeRate::orderBy('sort_order')->get(),
        ]);
    }

    public function storeExchangeRate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset' => ['required', 'string', 'max:20'],
            'buy_rate_ngn' => ['required', 'numeric', 'min:0'],
            'sell_rate_ngn' => ['required', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'processing_time' => ['nullable', 'string', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        ExchangeRate::updateOrCreate(
            ['asset' => strtoupper($data['asset'])],
            [
                'buy_rate_ngn' => $data['buy_rate_ngn'],
                'sell_rate_ngn' => $data['sell_rate_ngn'],
                'minimum_amount' => $data['minimum_amount'] ?? null,
                'maximum_amount' => $data['maximum_amount'] ?? null,
                'processing_time' => $data['processing_time'] ?? null,
                'is_featured' => $request->boolean('is_featured'),
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $data['sort_order'] ?? 0,
            ]
        );

        return back()->with('status', 'Exchange rate saved.');
    }

    public function destroyExchangeRate(ExchangeRate $exchangeRate): RedirectResponse
    {
        $exchangeRate->delete();

        return back()->with('status', 'Rate deleted.');
    }
}
