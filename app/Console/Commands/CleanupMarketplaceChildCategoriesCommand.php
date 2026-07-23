<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Listing;
use App\Models\MarketplaceProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Remove legacy child category rows after marketplace_products soak.
 * Default is dry-run. Never deletes a child while any listing still references its id.
 */
class CleanupMarketplaceChildCategoriesCommand extends Command
{
    protected $signature = 'marketplace:cleanup-child-categories
                            {--force : Actually delete child category rows that have been migrated}
                            {--days=14 : Only delete children created at least this many days ago}';

    protected $description = 'Remove legacy child categories after marketplace_products migration (dry-run by default)';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $days = max(0, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $children = Category::query()->whereNotNull('parent_id')->orderBy('id')->get();

        if ($children->isEmpty()) {
            $this->info('No child categories found.');

            return self::SUCCESS;
        }

        $deletable = [];
        $blocked = [];

        foreach ($children as $child) {
            $product = MarketplaceProduct::query()->where('slug', $child->slug)->first();

            if (! $product) {
                $blocked[] = [$child->id, $child->slug, 'No matching marketplace_product slug'];
                continue;
            }

            // Remap any listings still pointing at this child to the product parent.
            Listing::query()
                ->where('category_id', $child->id)
                ->update(['category_id' => $product->category_id]);

            $stillReferencing = Listing::query()->where('category_id', $child->id)->count();
            if ($stillReferencing > 0) {
                $blocked[] = [$child->id, $child->slug, "{$stillReferencing} listings still reference this category_id"];
                continue;
            }

            $unmigrated = Listing::query()
                ->whereNull('marketplace_product_id')
                ->where(function ($q) use ($child) {
                    $q->where('category_id', $child->id)
                        ->orWhere('category', $child->slug);
                })
                ->count();
            if ($unmigrated > 0) {
                $blocked[] = [$child->id, $child->slug, "{$unmigrated} listings lack marketplace_product_id"];
                continue;
            }

            if ($days > 0 && $child->created_at && $child->created_at->greaterThan($cutoff)) {
                $blocked[] = [$child->id, $child->slug, "Younger than {$days} days (created {$child->created_at->toDateString()})"];
                continue;
            }

            $deletable[] = $child;
        }

        $this->info('Child categories total: '.$children->count());
        $this->info('Safe to remove: '.count($deletable));
        $this->info('Blocked: '.count($blocked));
        $this->info("Soak days: {$days}");

        if ($blocked !== []) {
            $this->table(['id', 'slug', 'reason'], $blocked);
        }

        if ($deletable === []) {
            $this->warn('Nothing to delete.');

            return self::SUCCESS;
        }

        $this->table(
            ['id', 'slug', 'parent_id', 'product_id'],
            collect($deletable)->map(fn (Category $c) => [
                $c->id,
                $c->slug,
                $c->parent_id,
                MarketplaceProduct::query()->where('slug', $c->slug)->value('id'),
            ])->all()
        );

        if (! $force) {
            $this->warn('Dry-run only. Re-run with --force to delete.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($deletable) {
            foreach ($deletable as $child) {
                $child->delete();
            }
        });

        $this->info('Deleted '.count($deletable).' child category rows.');

        return self::SUCCESS;
    }
}
