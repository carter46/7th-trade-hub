<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('support_tickets')) {
            DB::table('support_tickets')
                ->where('status', 'in_progress')
                ->update(['status' => 'pending']);
        }
    }

    public function down(): void
    {
        // Irreversible status normalization.
    }
};
