<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Read-only report of database-managed copy that still mentions the removed
 * crypto exchange, escrow or peer marketplace. Nothing is rewritten here —
 * admins edit reported rows in the existing admin screens.
 */
class ScanLegacyCopyCommand extends Command
{
    protected $signature = 'content:scan-legacy-copy
        {--strict : Exit with a failure code when any matches are found}';

    protected $description = 'Report DB-managed copy that still mentions crypto, escrow or the peer marketplace (read-only)';

    /** Table => candidate copy columns; columns missing from the live schema are skipped. */
    private const TARGETS = [
        'system_settings' => ['value'],
        'catalog_page_contents' => ['short_description', 'hero_title', 'hero_subtitle', 'benefits', 'faq'],
        'service_categories' => ['name', 'short_description', 'hero_title', 'hero_subtitle', 'benefits', 'faq', 'cta_label'],
        'product_types' => ['name', 'short_description', 'hero_title', 'hero_subtitle', 'benefits', 'faq'],
        'platform_categories' => ['name', 'description', 'short_description', 'hero_title', 'hero_subtitle', 'benefits', 'faq'],
        'platform_products' => ['title', 'short_description', 'description', 'features', 'whats_included', 'faqs', 'support_text'],
        'social_links' => ['platform', 'url'],
        'email_identities' => ['from_name'],
    ];

    private const PATTERN = '/\b(crypto\w*|bitcoin|btc|usdt|blockchain|otc|exchange|escrow\w*|marketplace\w*|listings?|sellers?|buyer protection|buy and sell|buy, sell|traders?|watchlists?)\b/i';

    /** Phrases that match the pattern but describe features that still exist. */
    private const ALLOWED = ['/website[\s_-]?listings?/i'];

    public function handle(): int
    {
        $rows = [];

        foreach (self::TARGETS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = array_values(array_filter($columns, fn ($c) => Schema::hasColumn($table, $c)));
            if ($columns === []) {
                continue;
            }

            $identifier = $table === 'system_settings' ? 'key' : 'id';

            DB::table($table)
                ->select(array_unique(array_merge(['id', $identifier], $columns)))
                ->orderBy('id')
                ->chunk(200, function ($records) use ($table, $columns, $identifier, &$rows) {
                    foreach ($records as $record) {
                        foreach ($columns as $column) {
                            $snippet = $this->match((string) ($record->{$column} ?? ''));
                            if ($snippet !== null) {
                                $rows[] = [$table, (string) $record->{$identifier}, $column, $snippet];
                            }
                        }
                    }
                });
        }

        if ($rows === []) {
            $this->info('No legacy crypto / escrow / marketplace copy found in database-managed content.');

            return self::SUCCESS;
        }

        $this->table(['Table', 'Row', 'Column', 'Snippet'], $rows);
        $this->warn(count($rows).' match(es). Review each row in the admin screens; this command changes nothing.');

        return $this->option('strict') ? self::FAILURE : self::SUCCESS;
    }

    private function match(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $text = preg_replace(self::ALLOWED, ' ', $value) ?? $value;

        if (! preg_match(self::PATTERN, $text, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start = max(0, $m[0][1] - 40);

        return Str::of(substr($text, $start, 120))->squish()->limit(100)->toString();
    }
}
