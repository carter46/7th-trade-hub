<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog rename updated platform_products.title, but My Tools / admin user tools
 * read user_tools.display_name (snapshotted at purchase). Sync those labels.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_products') || ! Schema::hasTable('user_tools')) {
            return;
        }

        $products = DB::table('platform_products')
            ->whereIn('slug', ['online-banking-v1', 'online-banking-v2', 'online-banking-v3'])
            ->get(['id', 'title', 'slug']);

        foreach ($products as $product) {
            $title = (string) $product->title;

            $tools = DB::table('user_tools')
                ->where('platform_product_id', $product->id)
                ->get(['id', 'instance_sequence', 'display_name']);

            foreach ($tools as $tool) {
                $sequence = (int) ($tool->instance_sequence ?? 1);
                $expected = $sequence > 1 ? $title.' #'.$sequence : $title;

                if ((string) $tool->display_name === $expected) {
                    continue;
                }

                DB::table('user_tools')->where('id', $tool->id)->update([
                    'display_name' => $expected,
                    'updated_at' => now(),
                ]);
            }

            if (! Schema::hasTable('order_items') || ! Schema::hasColumn('order_items', 'platform_product_id')) {
                continue;
            }

            $items = DB::table('order_items')
                ->where('platform_product_id', $product->id)
                ->get(['id', 'options']);

            foreach ($items as $item) {
                $options = json_decode((string) ($item->options ?? '{}'), true);
                if (! is_array($options)) {
                    $options = [];
                }

                $current = $options['product_title'] ?? null;
                if ($current === $title) {
                    continue;
                }

                // Only rewrite known legacy banking titles (keep unrelated custom labels).
                if ($current !== null && ! $this->isLegacyOnlineBankingTitle((string) $current)) {
                    continue;
                }

                $options['product_title'] = $title;

                DB::table('order_items')->where('id', $item->id)->update([
                    'options' => json_encode($options),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Irreversible label sync — catalog title remains source of truth.
    }

    private function isLegacyOnlineBankingTitle(string $title): bool
    {
        $normalized = strtolower(trim($title));

        return in_array($normalized, [
            'online banking website',
            'online banking',
            'starter business site',
            'online banking v1',
            'online banking v2',
            'online banking v3',
        ], true)
            || str_starts_with($normalized, 'online banking website #')
            || str_starts_with($normalized, 'online banking #')
            || str_starts_with($normalized, 'starter business site #')
            || preg_match('/^online banking v[123]( #\d+)?$/', $normalized) === 1;
    }
};
