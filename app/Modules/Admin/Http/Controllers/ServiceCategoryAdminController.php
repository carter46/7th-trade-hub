<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use App\Support\FaqNormalizer;
use App\Support\SortOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCategoryAdminController extends Controller
{
    public function __construct(
        private MediaUsageService $mediaUsages,
        private MediaPathService $mediaPaths,
    ) {}

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
        $siblingMax = ServiceCategory::query()->system()->count();

        return view('dashboard.admin.service-categories.edit', [
            'category' => $serviceCategory,
            'siblingMax' => $siblingMax,
        ]);
    }

    public function update(Request $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        if (! $serviceCategory->isSystem()) {
            return redirect()
                ->route('admin.service-categories')
                ->with('error', __('That category is not a fixed platform category.'));
        }

        $siblingMax = ServiceCategory::query()->system()->count();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:'.$siblingMax],
            'short_description' => ['nullable', 'string', 'max:500'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['nullable', 'string', 'max:500'],
            'faq' => ['nullable', 'array'],
            'faq.*.q' => ['nullable', 'string', 'max:500'],
            'faq.*.a' => ['nullable', 'string', 'max:2000'],
            'faq.*.open' => ['nullable', 'boolean'],
            'card_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
        ]);

        $cardMediaId = isset($data['card_media_id']) ? (int) $data['card_media_id'] : null;
        $path = $this->mediaPaths->legacyPathFromMediaId($cardMediaId);

        $serviceCategory->update([
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active'),
            'short_description' => $data['short_description'] ?? null,
            'hero_title' => $data['hero_title'] ?? null,
            'hero_subtitle' => $data['hero_subtitle'] ?? null,
            'benefits' => FaqNormalizer::stringList($data['benefits'] ?? null),
            'faq' => FaqNormalizer::fromRequest($data['faq'] ?? null),
            'card_media_id' => $cardMediaId,
            'banner_media_id' => $cardMediaId,
            'card_image' => $path,
            'banner_image' => $path,
        ]);

        $this->mediaUsages->syncUsages($serviceCategory, [
            'card' => $cardMediaId,
            'banner' => $cardMediaId,
        ]);

        SortOrder::move(
            $serviceCategory,
            (int) $data['sort_order'],
            ServiceCategory::query()->system()
        );

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
