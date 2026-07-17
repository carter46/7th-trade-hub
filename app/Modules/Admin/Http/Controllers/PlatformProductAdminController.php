<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Enums\PlatformProductStatus;
use App\Enums\PlatformProductType;
use App\Http\Controllers\Controller;
use App\Models\PlatformCategory;
use App\Models\PlatformProduct;
use App\Models\PlatformProductImage;
use App\Models\PlatformProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformProductAdminController extends Controller
{
    public function index(Request $request): View
    {
        $products = PlatformProduct::query()
            ->with('category')
            ->when($request->filled('type'), fn ($q) => $q->where('product_type', $request->get('type')))
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('dashboard.admin.platform-products', [
            'products' => $products,
            'types' => PlatformProductType::cases(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.platform-product-form', [
            'product' => new PlatformProduct(['status' => PlatformProductStatus::Draft, 'base_price' => 0]),
            'types' => PlatformProductType::cases(),
            'categories' => PlatformCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $product = PlatformProduct::create($data);
        $this->syncVariants($product, $request->input('variants', []), (float) $data['base_price']);
        $this->syncImages($product, (string) $request->input('gallery_paths', ''));

        return redirect()->route('admin.platform-products')->with('status', 'Product created.');
    }

    public function edit(PlatformProduct $platformProduct): View
    {
        return view('dashboard.admin.platform-product-form', [
            'product' => $platformProduct->load(['variants', 'images']),
            'types' => PlatformProductType::cases(),
            'categories' => PlatformCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PlatformProduct $platformProduct): RedirectResponse
    {
        $data = $this->validated($request, $platformProduct->id);
        if (empty($data['slug'])) {
            $data['slug'] = $platformProduct->slug ?: Str::slug($data['title']);
        }
        $platformProduct->update($data);
        $this->syncVariants($platformProduct, $request->input('variants', []), (float) $data['base_price']);
        $this->syncImages($platformProduct, (string) $request->input('gallery_paths', ''));

        return redirect()->route('admin.platform-products')->with('status', 'Product updated.');
    }

    public function destroy(PlatformProduct $platformProduct): RedirectResponse
    {
        $platformProduct->delete();

        return back()->with('status', 'Product deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('platform_products', 'slug')->ignore($ignoreId),
            ],
            'product_type' => ['required', Rule::enum(PlatformProductType::class)],
            'platform_category_id' => ['nullable', 'exists:platform_categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['sometimes', 'boolean'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'hero_image' => ['nullable', 'string', 'max:500'],
            'demo_url' => ['nullable', 'url'],
            'demo_username' => ['nullable', 'string', 'max:255'],
            'demo_password' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:100'],
            'framework' => ['nullable', 'string', 'max:100'],
            'support_period' => ['nullable', 'string', 'max:100'],
            'support_text' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'features_text' => ['nullable', 'string'],
            'requirements_text' => ['nullable', 'string'],
            'whats_included_text' => ['nullable', 'string'],
            'faqs_text' => ['nullable', 'string'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.duration_months' => ['nullable', 'integer', 'min:1'],
            'variants.*.is_default' => ['sometimes', 'boolean'],
            'gallery_paths' => ['nullable', 'string'],
        ]);

        return [
            'title' => $data['title'],
            'slug' => $data['slug'] ?? null,
            'product_type' => $data['product_type'] instanceof PlatformProductType
                ? $data['product_type']->value
                : $data['product_type'],
            'platform_category_id' => $data['platform_category_id'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'base_price' => $data['base_price'],
            'hero_image' => $data['hero_image'] ?? null,
            'demo_url' => $data['demo_url'] ?? null,
            'demo_username' => $data['demo_username'] ?? null,
            'demo_password' => $data['demo_password'] ?? null,
            'industry' => $data['industry'] ?? null,
            'framework' => $data['framework'] ?? null,
            'support_period' => $data['support_period'] ?? null,
            'support_text' => $data['support_text'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_featured' => $request->boolean('is_featured'),
            'is_responsive' => $request->boolean('is_responsive', true),
            'is_seo_ready' => $request->boolean('is_seo_ready'),
            'features' => $this->linesToList($data['features_text'] ?? ''),
            'requirements' => $this->linesToList($data['requirements_text'] ?? ''),
            'whats_included' => $this->linesToList($data['whats_included_text'] ?? ''),
            'faqs' => $this->parseFaqs($data['faqs_text'] ?? ''),
        ];
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

    private function linesToList(string $text): ?array
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return $lines === [] ? null : $lines;
    }

    private function syncVariants(PlatformProduct $product, array $variants, float $fallbackPrice): void
    {
        $keptIds = [];
        $sort = 0;
        $hasDefault = false;

        foreach ($variants as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $price = isset($row['price']) && $row['price'] !== '' ? (float) $row['price'] : $fallbackPrice;
            $isDefault = ! empty($row['is_default']);
            if ($isDefault) {
                $hasDefault = true;
            }

            $attrs = [
                'platform_product_id' => $product->id,
                'name' => $name,
                'label' => $name,
                'duration_months' => $row['duration_months'] ?? null,
                'price' => $price,
                'sort_order' => $sort++,
                'is_default' => $isDefault,
                'is_active' => true,
            ];

            if (! empty($row['id'])) {
                $variant = PlatformProductVariant::query()
                    ->where('platform_product_id', $product->id)
                    ->where('id', $row['id'])
                    ->first();
                if ($variant) {
                    $variant->update($attrs);
                    $keptIds[] = $variant->id;
                    continue;
                }
            }

            $variant = PlatformProductVariant::create($attrs + [
                'sku' => $product->slug.'-'.Str::random(4),
            ]);
            $keptIds[] = $variant->id;
        }

        if ($keptIds === []) {
            $variant = PlatformProductVariant::updateOrCreate(
                ['sku' => $product->slug.'-std'],
                [
                    'platform_product_id' => $product->id,
                    'name' => 'Standard',
                    'label' => 'Standard',
                    'price' => $fallbackPrice,
                    'is_default' => true,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
            $keptIds[] = $variant->id;
        } elseif (! $hasDefault) {
            PlatformProductVariant::where('id', $keptIds[0])->update(['is_default' => true]);
        }

        PlatformProductVariant::query()
            ->where('platform_product_id', $product->id)
            ->whereNotIn('id', $keptIds)
            ->delete();
    }

    private function syncImages(PlatformProduct $product, string $galleryPaths): void
    {
        $paths = collect(preg_split('/\r\n|\r|\n/', $galleryPaths) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        $product->images()->delete();
        foreach ($paths as $i => $path) {
            PlatformProductImage::create([
                'platform_product_id' => $product->id,
                'path' => $path,
                'alt' => $product->title,
                'sort_order' => $i,
            ]);
        }
    }
}
