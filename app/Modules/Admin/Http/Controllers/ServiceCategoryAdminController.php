<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use App\Support\FaqNormalizer;
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
            'banner_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
            'card_media_id' => ['nullable', 'integer', $this->mediaPaths->existsRule()],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['nullable', 'string', 'max:500'],
            'faq' => ['nullable', 'array'],
            'faq.*.q' => ['nullable', 'string', 'max:500'],
            'faq.*.a' => ['nullable', 'string'],
            'faq.*.open' => ['nullable'],
        ]);

        $bannerMediaId = isset($data['banner_media_id']) ? (int) $data['banner_media_id'] : null;
        $cardMediaId = isset($data['card_media_id']) ? (int) $data['card_media_id'] : null;

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
            'banner_media_id' => $bannerMediaId,
            'card_media_id' => $cardMediaId,
            'banner_image' => $this->mediaPaths->legacyPathFromMediaId($bannerMediaId),
            'card_image' => $this->mediaPaths->legacyPathFromMediaId($cardMediaId),
            'benefits' => FaqNormalizer::stringList($data['benefits'] ?? null),
            'faq' => FaqNormalizer::fromRequest($data['faq'] ?? null),
        ];
    }

    private function syncMedia(ServiceCategory $category, array $data): void
    {
        $this->mediaUsages->syncUsages($category, [
            'banner' => $data['banner_media_id'] ?? null,
            'card' => $data['card_media_id'] ?? null,
        ]);

        // Keep Catalog Pages override row in sync so stale config paths cannot win.
        if (\Illuminate\Support\Facades\Schema::hasTable('catalog_page_contents')) {
            \App\Models\CatalogPageContent::query()->updateOrCreate(
                ['scope' => 'group', 'key' => $category->slug],
                [
                    'banner_media_id' => $data['banner_media_id'] ?? null,
                    'card_media_id' => $data['card_media_id'] ?? null,
                    'banner_image' => $data['banner_image'] ?? null,
                    'card_image' => $data['card_image'] ?? null,
                ]
            );
        }
    }
}
