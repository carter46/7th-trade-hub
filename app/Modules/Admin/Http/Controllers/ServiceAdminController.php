<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUsageService;
use App\Support\FaqNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'categories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.services.create', [
            'service' => new ProductType(['is_active' => true, 'sort_order' => 0]),
            'categories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $service = ProductType::create($data);
        $this->syncMedia($service, $data);

        return redirect()
            ->route('admin.services')
            ->with('status', 'Service created.');
    }

    public function edit(ProductType $service): View
    {
        $service->load(['bannerMedia.variants', 'cardMedia.variants']);

        return view('dashboard.admin.services.edit', [
            'service' => $service,
            'categories' => ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ProductType $service): RedirectResponse
    {
        $data = $this->validated($request, $service->id);
        if (empty($data['slug'])) {
            $data['slug'] = $service->slug ?: Str::slug($data['name']);
        }

        $service->update($data);
        $this->syncMedia($service, $data);

        return redirect()
            ->route('admin.services')
            ->with('status', 'Service updated.');
    }

    public function toggle(ProductType $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('status', 'Service '.($service->is_active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(ProductType $service): RedirectResponse
    {
        $this->mediaUsages->detachAllFor($service);
        $service->delete();

        return redirect()
            ->route('admin.services')
            ->with('status', 'Service deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_types', 'slug')->ignore($ignoreId),
            ],
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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
            'service_category_id' => $data['service_category_id'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
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

    private function syncMedia(ProductType $service, array $data): void
    {
        $this->mediaUsages->syncUsages($service, [
            'banner' => $data['banner_media_id'] ?? null,
            'card' => $data['card_media_id'] ?? null,
        ]);

        if (\Illuminate\Support\Facades\Schema::hasTable('catalog_page_contents')) {
            \App\Models\CatalogPageContent::query()->updateOrCreate(
                ['scope' => 'type', 'key' => $service->slug],
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
