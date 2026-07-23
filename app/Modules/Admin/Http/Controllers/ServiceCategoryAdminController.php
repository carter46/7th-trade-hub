<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            ->with(['cardMedia.variants', 'bannerMedia.variants'])
            ->withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('dashboard.admin.service-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('dashboard.admin.service-categories.create', [
            'category' => new ServiceCategory([
                'is_active' => true,
                'mode' => 'catalog',
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $category = ServiceCategory::create($data);
        $this->syncMedia($category, $data);

        return redirect()
            ->route('admin.service-categories')
            ->with('status', 'Service category created.');
    }

    public function edit(ServiceCategory $serviceCategory): View
    {
        $serviceCategory->load(['bannerMedia.variants', 'cardMedia.variants']);

        return view('dashboard.admin.service-categories.edit', [
            'category' => $serviceCategory,
        ]);
    }

    public function update(Request $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $data = $this->validated($request, $serviceCategory->id);
        if (empty($data['slug'])) {
            $data['slug'] = $serviceCategory->slug ?: Str::slug($data['name']);
        }

        $serviceCategory->update($data);
        $this->syncMedia($serviceCategory, $data);

        return redirect()
            ->route('admin.service-categories')
            ->with('status', 'Service category updated.');
    }

    public function toggle(ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update(['is_active' => ! $serviceCategory->is_active]);

        return back()->with('status', 'Category '.($serviceCategory->is_active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(ServiceCategory $serviceCategory): RedirectResponse
    {
        $this->mediaUsages->detachAllFor($serviceCategory);
        $serviceCategory->delete();

        return redirect()
            ->route('admin.service-categories')
            ->with('status', 'Service category deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('service_categories', 'slug')->ignore($ignoreId),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'mode' => ['required', Rule::in(['catalog', 'marketplace_link'])],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'card_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
        ]);

        // Card image is the source of truth; banner mirrors it for public headers.
        $cardMediaId = isset($data['card_media_id']) ? (int) $data['card_media_id'] : null;
        $path = $this->mediaPaths->legacyPathFromMediaId($cardMediaId);

        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'mode' => $data['mode'],
            'cta_label' => $data['cta_label'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'hero_title' => $data['hero_title'] ?? null,
            'hero_subtitle' => $data['hero_subtitle'] ?? null,
            'card_media_id' => $cardMediaId,
            'banner_media_id' => $cardMediaId,
            'card_image' => $path,
            'banner_image' => $path,
        ];
    }

    private function syncMedia(ServiceCategory $category, array $data): void
    {
        $mediaId = $data['card_media_id'] ?? null;

        $this->mediaUsages->syncUsages($category, [
            'card' => $mediaId,
            'banner' => $mediaId,
        ]);
    }
}
