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
            if (! Schema::hasColumn('domain_registrations', 'registrant_contact')) {
                $table->json('registrant_contact')->nullable()->after('provider_currency_at_checkout');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('domain_registrations') && Schema::hasColumn('domain_registrations', 'registrant_contact')) {
            Schema::table('domain_registrations', function (Blueprint $table) {
                $table->dropColumn('registrant_contact');
            });
        }
    }
};
