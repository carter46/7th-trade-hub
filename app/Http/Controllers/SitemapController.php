<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\MarketplaceProduct;
use App\Models\PlatformProduct;
use App\Modules\Catalog\Services\CatalogBrowseService;
use App\Support\HelpContent;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Throwable;

class SitemapController extends Controller
{
    public function __construct(private CatalogBrowseService $browse) {}

    public function index(): Response
    {
        $urls = Cache::remember('sitemap.xml.v2', now()->addHour(), fn () => $this->buildUrls());

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @return list<array{loc: string, lastmod?: string, priority: string, changefreq: string}>
     */
    private function buildUrls(): array
    {
        $urls = [];

        $staticRoutes = [
            'home' => ['priority' => '1.0', 'changefreq' => 'daily'],
            'marketplace' => ['priority' => '0.9', 'changefreq' => 'daily'],
            'about' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'help' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'contact' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'services' => ['priority' => '0.8', 'changefreq' => 'weekly'],
            'templates' => ['priority' => '0.7', 'changefreq' => 'weekly'],
            'website-listings' => ['priority' => '0.7', 'changefreq' => 'weekly'],
            'exchange' => ['priority' => '0.7', 'changefreq' => 'weekly'],
            'legal' => ['priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        foreach ($staticRoutes as $name => $meta) {
            if (Route::has($name)) {
                $this->push($urls, route($name), $meta);
            }
        }

        $this->push($urls, route('legal', ['doc' => 'terms']), ['priority' => '0.3', 'changefreq' => 'yearly']);
        $this->push($urls, route('legal', ['doc' => 'privacy']), ['priority' => '0.3', 'changefreq' => 'yearly']);

        try {
            foreach (array_keys(HelpContent::all()) as $helpSlug) {
                $this->push($urls, route('help.article', $helpSlug), [
                    'priority' => '0.45',
                    'changefreq' => 'monthly',
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('sitemap.help_failed', ['message' => $e->getMessage()]);
        }

        foreach (array_keys(config('catalog.groups', [])) as $groupSlug) {
            $this->push($urls, route('services.segment', $groupSlug), [
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ]);
        }

        foreach (array_keys(config('catalog.types', [])) as $typeKey) {
            $this->push($urls, route('services.segment', $typeKey), [
                'priority' => '0.65',
                'changefreq' => 'weekly',
            ]);
        }

        try {
            Category::query()
                ->marketplace()
                ->active()
                ->roots()
                ->select(['slug', 'updated_at'])
                ->orderBy('sort_order')
                ->chunk(100, function ($categories) use (&$urls) {
                    foreach ($categories as $category) {
                        $this->push($urls, route('marketplace.show', $category->slug), [
                            'lastmod' => $category->updated_at,
                            'priority' => '0.75',
                            'changefreq' => 'weekly',
                        ]);
                    }
                });
        } catch (Throwable $e) {
            Log::warning('sitemap.categories_failed', ['message' => $e->getMessage()]);
        }

        try {
            MarketplaceProduct::query()
                ->active()
                ->with('category:id,slug')
                ->select(['id', 'slug', 'category_id', 'updated_at'])
                ->orderBy('sort_order')
                ->chunk(100, function ($products) use (&$urls) {
                    foreach ($products as $product) {
                        if (! $product->category) {
                            continue;
                        }
                        $this->push($urls, route('marketplace.product', [
                            'category' => $product->category->slug,
                            'product' => $product->slug,
                        ]), [
                            'lastmod' => $product->updated_at,
                            'priority' => '0.7',
                            'changefreq' => 'weekly',
                        ]);
                    }
                });
        } catch (Throwable $e) {
            Log::warning('sitemap.marketplace_products_failed', ['message' => $e->getMessage()]);
        }

        try {
            Listing::published()
                ->select(['slug', 'updated_at'])
                ->orderByDesc('updated_at')
                ->chunk(100, function ($listings) use (&$urls) {
                    foreach ($listings as $listing) {
                        $this->push($urls, route('marketplace.show', $listing->slug), [
                            'lastmod' => $listing->updated_at,
                            'priority' => '0.8',
                            'changefreq' => 'weekly',
                        ]);
                    }
                });
        } catch (Throwable $e) {
            Log::warning('sitemap.listings_failed', ['message' => $e->getMessage()]);
        }

        try {
            PlatformProduct::published()
                ->with(['productType.serviceCategory'])
                ->select(['id', 'slug', 'product_type', 'product_type_id', 'updated_at'])
                ->orderByDesc('updated_at')
                ->chunk(100, function ($products) use (&$urls) {
                    foreach ($products as $product) {
                        try {
                            $this->push($urls, $this->browse->productUrl($product), [
                                'lastmod' => $product->updated_at,
                                'priority' => '0.75',
                                'changefreq' => 'weekly',
                            ]);
                        } catch (Throwable $e) {
                            Log::warning('sitemap.platform_product_url_failed', [
                                'slug' => $product->slug,
                                'message' => $e->getMessage(),
                            ]);
                        }
                    }
                });
        } catch (Throwable $e) {
            Log::warning('sitemap.platform_products_failed', ['message' => $e->getMessage()]);
        }

        return $urls;
    }

    /**
     * @param  list<array{loc: string, lastmod?: string, priority: string, changefreq: string}>  $urls
     * @param  array{lastmod?: mixed, priority?: string, changefreq?: string}  $meta
     */
    private function push(array &$urls, string $loc, array $meta = []): void
    {
        if ($loc === '' || ! str_starts_with($loc, 'http')) {
            return;
        }

        $entry = [
            'loc' => $loc,
            'priority' => $meta['priority'] ?? '0.5',
            'changefreq' => $meta['changefreq'] ?? 'weekly',
        ];

        if (! empty($meta['lastmod'])) {
            $lastmod = $this->formatLastmod($meta['lastmod']);
            if ($lastmod !== null) {
                $entry['lastmod'] = $lastmod;
            }
        }

        $urls[] = $entry;
    }

    private function formatLastmod(mixed $value): ?string
    {
        try {
            if ($value instanceof CarbonInterface) {
                return $value->toAtomString();
            }

            return Carbon::parse($value)->toAtomString();
        } catch (Throwable) {
            return null;
        }
    }
}
