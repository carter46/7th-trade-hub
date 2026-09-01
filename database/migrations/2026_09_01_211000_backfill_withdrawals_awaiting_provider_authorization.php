<?php

use App\Models\Withdrawal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $query = DB::table('withdrawals')
            ->where('provider_status', 'PENDING_AUTHORIZATION')
            ->where('internal_status', 'processing')
            ->whereNotIn('status', ['completed', 'rejected', 'failed']);

        $ids = (clone $query)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('withdrawals')
            ->whereIn('id', $ids)
            ->update(['internal_status' => Withdrawal::INTERNAL_AWAITING_PROVIDER_AUTH]);

        DB::table('withdrawals')
            ->whereIn('id', $ids)
            ->whereNull('provider')
            ->update(['provider' => 'monnify']);
    }

    public function down(): void
    {
        // Non-destructive backfill; no rollback.
    }
};
