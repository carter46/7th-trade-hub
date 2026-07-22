<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceCategoryAdminController extends Controller
{
    public function index(): View
    {
        $categories = ServiceCategory::query()
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

        ServiceCategory::create($data);

        return redirect()
            ->route('admin.service-categories')
            ->with('status', 'Service category created.');
    }

    public function edit(ServiceCategory $serviceCategory): View
    {
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
            'banner_image' => ['nullable', 'string', 'max:255'],
            'card_image' => ['nullable', 'string', 'max:255'],
            'benefits_text' => ['nullable', 'string'],
            'faq_text' => ['nullable', 'string'],
        ]);

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
