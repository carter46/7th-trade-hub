<?php

namespace Database\Seeders\Demo;

use App\Models\Favorite;
use App\Models\Listing;
use App\Models\ListingVersion;
use App\Models\MarketplaceProduct;
use App\Models\Message;
use App\Models\PlatformProduct;
use App\Models\Watchlist;
use Database\Seeders\Demo\Support\DemoContext;
use Database\Seeders\Demo\Support\DemoTimeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoMarketplaceSeeder extends Seeder
{
    public function run(DemoContext $ctx, DemoTimeline $timeline): void
    {
        $moderator = $ctx->admin('moderator');
        $products = MarketplaceProduct::query()->where('is_active', true)->orderBy('id')->get();
        if ($products->isEmpty()) {
            throw new \RuntimeException('DemoMarketplaceSeeder requires MarketplaceProduct rows.');
        }

        $sellers = collect([
            $ctx->member('michael'),
            $ctx->member('sarah'),
        ]);
        foreach ($ctx->members() as $key => $user) {
            if (str_starts_with($key, 'filler') && ($user->id % 3 === 0)) {
                $sellers->push($user);
            }
        }
        $sellers = $sellers->unique('id')->values();

        $statusCycle = [
            'published', 'published', 'published', 'published',
            'pending_review', 'draft', 'rejected', 'suspended',
            'sold', 'archived', 'published', 'pending_review',
        ];

        $created = 0;
        $existing = Listing::query()->count();
        $target = max(55, 100 - $existing);

        for ($i = 0; $i < $target; $i++) {
            $seller = $sellers[$i % $sellers->count()];
            $product = $products[$i % $products->count()];
            $status = $statusCycle[$i % count($statusCycle)];
            $at = $timeline->monthsAgo(min(6, (int) ($seller->created_at?->diffInMonths(now()) ?: 3)), 5 + ($i % 20), 12);

            $title = match ($status) {
                'pending_review' => 'Pending review: '.$product->name.' lot #'.($i + 1),
                'draft' => 'Draft '.$product->name.' concept #'.($i + 1),
                'rejected' => 'Needs changes '.$product->name.' #'.($i + 1),
                'suspended' => 'Suspended '.$product->name.' #'.($i + 1),
                'sold' => 'Sold '.$product->name.' inventory #'.($i + 1),
                'archived' => 'Archived '.$product->name.' #'.($i + 1),
                default => 'Live '.$product->name.' offer #'.($i + 1),
            };

            $listing = Listing::query()->create([
                'user_id' => $seller->id,
                'marketplace_product_id' => $product->id,
                'category_id' => $product->category_id,
                'title' => $title,
                'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
                'description' => 'Demo listing for '.$product->name.'. Escrow-ready delivery with docs.',
                'price' => [8500, 12000, 25000, 45000, 6500][$i % 5],
                'is_active' => $status === 'published',
                'status' => $status,
                'featured' => $i % 11 === 0 && $status === 'published',
            ]);
            $ctx->stamp($listing, $at);

            $versionStatus = match ($status) {
                'pending_review' => 'pending_review',
                'rejected' => 'rejected',
                'published', 'sold', 'suspended', 'archived' => 'approved',
                default => 'draft',
            };

            $version = ListingVersion::query()->create([
                'listing_id' => $listing->id,
                'version_number' => 1,
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'status' => $versionStatus,
                'submitted_at' => in_array($status, ['draft'], true) ? null : $at,
                'reviewed_by' => in_array($versionStatus, ['approved', 'rejected'], true) ? $moderator->id : null,
                'reviewed_at' => in_array($versionStatus, ['approved', 'rejected'], true) ? $at->copy()->addDay() : null,
            ]);
            $ctx->stamp($version, $at, [
                'submitted_at' => $version->submitted_at,
                'reviewed_at' => $version->reviewed_at,
            ]);

            if ($i % 17 === 0 && $status === 'archived') {
                $listing->delete(); // soft delete → trash
            }

            $created++;
        }

        // Engagement: watchlist / favorites / messages
        $alice = $ctx->member('alice');
        $published = Listing::query()->where('status', 'published')->where('is_active', true)->limit(12)->get();
        foreach ($published->take(6) as $idx => $listing) {
            Watchlist::query()->firstOrCreate([
                'user_id' => $alice->id,
                'listing_id' => $listing->id,
            ]);
            $ctx->track(Watchlist::query()->where('user_id', $alice->id)->where('listing_id', $listing->id)->first());
            Favorite::query()->firstOrCreate([
                'user_id' => $alice->id,
                'favoritable_type' => Listing::class,
                'favoritable_id' => $listing->id,
            ]);
            $ctx->track(Favorite::query()->where('user_id', $alice->id)->where('favoritable_type', Listing::class)->where('favoritable_id', $listing->id)->first());
            if ($idx < 3) {
                $msg = Message::query()->create([
                    'from_user_id' => $alice->id,
                    'to_user_id' => $listing->user_id,
                    'order_id' => null,
                    'subject' => 'Question about '.$listing->title,
                    'body' => 'Is this still available for escrow purchase this week?',
                    'folder' => 'inbox',
                ]);
                $ctx->track($msg);
            }
        }

        $platformProduct = PlatformProduct::query()->published()->first();
        if ($platformProduct) {
            Favorite::query()->firstOrCreate([
                'user_id' => $alice->id,
                'favoritable_type' => PlatformProduct::class,
                'favoritable_id' => $platformProduct->id,
            ]);
            $ctx->track(Favorite::query()->where('user_id', $alice->id)->where('favoritable_type', PlatformProduct::class)->where('favoritable_id', $platformProduct->id)->first());
        }

        $ctx->listingCount = $created;
        $ctx->note('✓ Marketplace listings created ('.$created.' with status mix + engagement)');
    }
}
