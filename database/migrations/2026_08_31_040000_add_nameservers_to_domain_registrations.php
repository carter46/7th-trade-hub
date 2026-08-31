<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('domain_registrations')) {
            return;
        }

        Schema::table('domain_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('domain_registrations', 'nameservers')) {
                $table->json('nameservers')->nullable()->after('registrant_contact');
            }
            if (! Schema::hasColumn('domain_registrations', 'nameservers_updated_at')) {
                $table->timestamp('nameservers_updated_at')->nullable()->after('nameservers');
            }
            if (! Schema::hasColumn('domain_registrations', 'nameservers_synced_at')) {
                $table->timestamp('nameservers_synced_at')->nullable()->after('nameservers_updated_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('domain_registrations')) {
            return;
        }

        Schema::table('domain_registrations', function (Blueprint $table) {
            foreach (['nameservers_synced_at', 'nameservers_updated_at', 'nameservers'] as $column) {
                if (Schema::hasColumn('domain_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
