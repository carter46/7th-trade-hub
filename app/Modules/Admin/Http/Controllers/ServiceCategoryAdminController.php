<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCategoryAdminController extends Controller
{
    public function index(): View
    {
        $categories = ServiceCategory::query()
            ->system()
            ->with(['cardMedia.variants', 'bannerMedia.variants'])
            ->withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('dashboard.admin.service-categories.index', compact('categories'));
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('admin.service-categories')
            ->with('error', __('Platform categories are fixed. You cannot add new ones.'));
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('admin.service-categories')
            ->with('error', __('Platform categories are fixed. You cannot add new ones.'));
    }

    public function edit(ServiceCategory $serviceCategory): View|RedirectResponse
    {
        if (! $serviceCategory->isSystem()) {
            return redirect()
                ->route('admin.service-categories')
                ->with('error', __('That category is not a fixed platform category.'));
        }

        $serviceCategory->load(['bannerMedia.variants', 'cardMedia.variants']);

        return view('dashboard.admin.service-categories.edit', [
            'category' => $serviceCategory,
        ]);
    }

    public function update(Request $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        if (! $serviceCategory->isSystem()) {
            return redirect()
                ->route('admin.service-categories')
                ->with('error', __('That category is not a fixed platform category.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $serviceCategory->update([
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.service-categories')
            ->with('status', 'Service category updated.');
    }

    public function toggle(ServiceCategory $serviceCategory): RedirectResponse
    {
        if (! $serviceCategory->isSystem()) {
            return back()->with('error', __('That category is not a fixed platform category.'));
        }

        $serviceCategory->update(['is_active' => ! $serviceCategory->is_active]);

        return back()->with('status', 'Category '.($serviceCategory->is_active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(): RedirectResponse
    {
        return redirect()
            ->route('admin.service-categories')
            ->with('error', __('Platform categories cannot be deleted.'));
    }
}
