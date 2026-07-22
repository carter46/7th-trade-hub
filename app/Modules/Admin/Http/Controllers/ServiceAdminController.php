<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceAdminController extends Controller
{
    public function index(Request $request): View
    {
        $services = ProductType::query()
            ->with('serviceCategory')
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

        ProductType::create($data);

        return redirect()
            ->route('admin.services')
            ->with('status', 'Service created.');
    }

    public function edit(ProductType $service): View
    {
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
            'banner_image' => ['nullable', 'string', 'max:255'],
            'card_image' => ['nullable', 'string', 'max:255'],
            'benefits_text' => ['nullable', 'string'],
            'faq_text' => ['nullable', 'string'],
        ]);

        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'service_category_id' => $data['service_category_id'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'short_description' => $data['short_description'] ?? null,
            'hero_title' => $data['hero_title'] ?? null,
            'hero_subtitle' => $data['hero_subtitle'] ?? null,
            'banner_image' => $data['banner_image'] ?? null,
            'card_image' => $data['card_image'] ?? null,
            'benefits' => $this->linesToList($data['benefits_text'] ?? ''),
            'faq' => $this->parseFaqs($data['faq_text'] ?? ''),
        ];
    }

    private function linesToList(string $text): ?array
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return $lines === [] ? null : $lines;
    }

    private function parseFaqs(string $text): ?array
    {
        $blocks = preg_split('/\n\s*\n/', trim($text)) ?: [];
        $faqs = [];
        foreach ($blocks as $block) {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $block) ?: [])));
            if (count($lines) < 2) {
                continue;
            }
            $faqs[] = [
                'q' => preg_replace('/^q:\s*/i', '', $lines[0]),
                'a' => preg_replace('/^a:\s*/i', '', implode(' ', array_slice($lines, 1))),
            ];
        }

        return $faqs === [] ? null : $faqs;
    }
}
