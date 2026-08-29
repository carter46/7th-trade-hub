<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use App\Support\FaqNormalizer;
use App\Support\SortOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceAdminController extends Controller
{
    public function __construct(
        private MediaUsageService $mediaUsages,
        private MediaPathService $mediaPaths,
    ) {}

    public function index(Request $request): View
    {
        $services = ProductType::query()
            ->with(['serviceCategory', 'cardMedia.variants', 'bannerMedia.variants'])
            ->withCount('products')
            ->whereHas('serviceCategory', fn ($q) => $q->system())
            ->when($request->filled('category'), fn ($q) => $q->where('service_category_id', $request->integer('category')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)->orWhere('slug', 'like', $term);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.admin.services.index', [
            'services' => $services,
            'categories' => ServiceCategory::query()->system()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('admin.services')
            ->with('error', __('Platform services are fixed. You cannot add new ones.'));
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('admin.services')
            ->with('error', __('Platform services are fixed. You cannot add new ones.'));
    }

    public function edit(ProductType $service): View|RedirectResponse
    {
        $service->load(['bannerMedia.variants', 'cardMedia.variants', 'serviceCategory']);
        if (! $service->serviceCategory?->isSystem()) {
            return redirect()
                ->route('admin.services')
                ->with('error', __('That service is not under a fixed platform category.'));
        }

        $siblingMax = ProductType::query()
            ->whereHas('serviceCategory', fn ($q) => $q->system())
            ->count();

        return view('dashboard.admin.services.edit', [
            'service' => $service,
            'siblingMax' => $siblingMax,
        ]);
    }

    public function update(Request $request, ProductType $service): RedirectResponse
    {
        $service->loadMissing('serviceCategory');
        if (! $service->serviceCategory?->isSystem()) {
            return redirect()
                ->route('admin.services')
                ->with('error', __('That service is not under a fixed platform category.'));
        }

        $siblings = ProductType::query()->whereHas('serviceCategory', fn ($q) => $q->system());
        $siblingMax = (clone $siblings)->count();

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

        $service->update([
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

        $this->mediaUsages->syncUsages($service, [
            'card' => $cardMediaId,
            'banner' => $cardMediaId,
        ]);

        SortOrder::move($service, (int) $data['sort_order'], $siblings);

        return redirect()
            ->route('admin.services')
            ->with('status', 'Service updated.');
    }

    public function toggle(ProductType $service): RedirectResponse
    {
        $service->loadMissing('serviceCategory');
        if (! $service->serviceCategory?->isSystem()) {
            return back()->with('error', __('That service is not under a fixed platform category.'));
        }

        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('status', 'Service '.($service->is_active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(): RedirectResponse
    {
        return redirect()
            ->route('admin.services')
            ->with('error', __('Platform services cannot be deleted.'));
    }
}
