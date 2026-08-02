<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exchange_rates') && ! Schema::hasColumn('exchange_rates', 'preferred_network_id')) {
            Schema::table('exchange_rates', function (Blueprint $table) {
                $table->string('preferred_network_id', 40)->nullable()->after('allowed_network_ids');
            });
        }

        $map = [
            'bitcoin' => 'bitcoin',
            'btc' => 'bitcoin',
            'ethereum' => 'ethereum',
            'eth' => 'ethereum',
            'erc20' => 'ethereum',
            'tron' => 'tron',
            'trc20' => 'tron',
            'bsc' => 'bsc',
            'bep20' => 'bsc',
            'bnb smart chain' => 'bsc',
            'bnb smart chain (bep20)' => 'bsc',
            'polygon' => 'polygon',
            'matic' => 'polygon',
            'base' => 'base',
            'arbitrum' => 'arbitrum',
            'arbitrum one' => 'arbitrum',
            'arb' => 'arbitrum',
            'solana' => 'solana',
            'sol' => 'solana',
        ];

        $normalize = function (?string $value) use ($map): ?string {
            if ($value === null || trim($value) === '') {
                return $value;
            }
            $key = strtolower(trim($value));

            return $map[$key] ?? $key;
        };

        foreach (['crypto_deposit_wallets', 'crypto_sell_requests', 'incoming_crypto_transactions'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'network')) {
                continue;
            }
            DB::table($table)->orderBy('id')->chunkById(200, function ($rows) use ($table, $normalize) {
                foreach ($rows as $row) {
                    $next = $normalize($row->network ?? null);
                    if ($next !== null && $next !== $row->network) {
                        DB::table($table)->where('id', $row->id)->update(['network' => $next]);
                    }
                }
            });
        }

        if (Schema::hasTable('exchange_rates') && Schema::hasColumn('exchange_rates', 'allowed_network_ids')) {
            $suggest = config('crypto.suggest_network_ids_by_coin', config('crypto.network_ids_by_coin', []));

            DB::table('exchange_rates')->orderBy('id')->chunkById(100, function ($rows) use ($normalize, $suggest) {
                foreach ($rows as $row) {
                    $raw = $row->allowed_network_ids;
                    if ($raw === null) {
                        $asset = strtoupper((string) $row->asset);
                        $defaults = $suggest[$asset] ?? [];
                        if ($defaults !== []) {
                            $defaults = array_values(array_unique(array_map(
                                fn ($id) => $normalize((string) $id),
                                $defaults
                            )));
                            DB::table('exchange_rates')->where('id', $row->id)->update([
                                'allowed_network_ids' => json_encode($defaults),
                            ]);
                            $row->allowed_network_ids = json_encode($defaults);
                            $raw = $row->allowed_network_ids;
                        } else {
                            continue;
                        }
                    }
                    $ids = is_string($raw) ? json_decode($raw, true) : $raw;
                    if (! is_array($ids)) {
                        continue;
                    }
                    $next = array_values(array_unique(array_filter(array_map(
                        fn ($id) => is_string($id) ? $normalize($id) : null,
                        $ids
                    ))));
                    $encoded = json_encode($next);
                    if ($encoded !== (is_string($raw) ? $raw : json_encode($ids))) {
                        DB::table('exchange_rates')->where('id', $row->id)->update([
                            'allowed_network_ids' => $encoded,
                        ]);
                    }
                }
            });

            // Soft-suggest preferred = first allowed (tron preferred for USDT when present).
            DB::table('exchange_rates')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    if (! empty($row->preferred_network_id)) {
                        continue;
                    }
                    $ids = json_decode((string) $row->allowed_network_ids, true);
                    if (! is_array($ids) || $ids === []) {
                        continue;
                    }
                    $preferred = $ids[0];
                    $asset = strtoupper((string) $row->asset);
                    if ($asset === 'USDT' && in_array('tron', $ids, true)) {
                        $preferred = 'tron';
                    }
                    if ($asset === 'ETH' && in_array('ethereum', $ids, true)) {
                        $preferred = 'ethereum';
                    }
                    if ($asset === 'BTC' && in_array('bitcoin', $ids, true)) {
                        $preferred = 'bitcoin';
                    }
                    DB::table('exchange_rates')->where('id', $row->id)->update([
                        'preferred_network_id' => $preferred,
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exchange_rates') && Schema::hasColumn('exchange_rates', 'preferred_network_id')) {
            Schema::table('exchange_rates', function (Blueprint $table) {
                $table->dropColumn('preferred_network_id');
            });
        }
    }
};
