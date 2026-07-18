<?php

namespace App\Http\Controllers;

use App\Enums\PlatformProductType;
use App\Models\Listing;
use App\Models\PlatformProduct;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $staticRoutes = [
            'home' => ['priority' => '1.0', 'changefreq' => 'daily'],
            'marketplace' => ['priority' => '0.9', 'changefreq' => 'daily'],
            'about' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'help' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'services' => ['priority' => '0.8', 'changefreq' => 'weekly'],
            'templates' => ['priority' => '0.7', 'changefreq' => 'weekly'],
            'website-listings' => ['priority' => '0.7', 'changefreq' => 'weekly'],
            'exchange' => ['priority' => '0.7', 'changefreq' => 'weekly'],
            'terms' => ['priority' => '0.3', 'changefreq' => 'yearly'],
            'privacy' => ['priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        $urls = [];

        foreach ($staticRoutes as $name => $meta) {
            if (Route::has($name)) {
                $urls[] = array_merge($meta, [
                    'loc' => route($name),
                ]);
            }
        }

        foreach (array_keys(config('catalog.groups', [])) as $groupSlug) {
            $urls[] = [
                'loc' => route('services.segment', $groupSlug),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        }

        foreach (array_keys(config('catalog.types', [])) as $typeKey) {
            $urls[] = [
                'loc' => route('services.segment', $typeKey),
                'priority' => '0.65',
                'changefreq' => 'weekly',
            ];
        }

        Listing::published()
            ->select(['slug', 'updated_at'])
            ->orderByDesc('updated_at')
            ->chunk(100, function ($listings) use (&$urls) {
                foreach ($listings as $listing) {
                    $urls[] = [
                        'loc' => route('marketplace.show', $listing->slug),
                        'lastmod' => $listing->updated_at,
                        'priority' => '0.8',
                        'changefreq' => 'weekly',
                    ];
                }
            });

        PlatformProduct::published()
            ->select(['slug', 'product_type', 'updated_at'])
            ->orderByDesc('updated_at')
            ->chunk(100, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    $urls[] = [
                        'loc' => $this->productUrl($product),
                        'lastmod' => $product->updated_at,
                        'priority' => '0.75',
                        'changefreq' => 'weekly',
                    ];
                }
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    private function productUrl(PlatformProduct $product): string
    {
        return match ($product->product_type) {
            PlatformProductType::DocumentTemplate => route('templates.show', $product->slug),
            PlatformProductType::WebsitePackage,
            PlatformProductType::WebsiteTemplate => route('website-listings.show', $product->slug),
            default => route('services.show', [
                'type' => $product->product_type->value,
                'productSlug' => $product->slug,
            ]),
        };
    }
}
