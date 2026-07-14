<?php

namespace App\Http\Controllers;

use App\Models\Listing;
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
            'services' => ['priority' => '0.6', 'changefreq' => 'weekly'],
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

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
