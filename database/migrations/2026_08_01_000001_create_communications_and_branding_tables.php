<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('integration_providers')) {
            Schema::create('integration_providers', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 60)->unique();
                $table->boolean('enabled')->default(false);
                $table->text('credentials')->nullable();
                $table->string('status', 40)->default('idle');
                $table->timestamp('last_sync_at')->nullable();
                $table->timestamp('last_tested_at')->nullable();
                $table->timestamp('last_success_at')->nullable();
                $table->timestamp('last_error_at')->nullable();
                $table->text('last_error')->nullable();
                $table->unsignedInteger('success_count')->default(0);
                $table->unsignedInteger('failure_count')->default(0);
                $table->unsignedInteger('avg_latency_ms')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('analytics_providers') && Schema::hasTable('integration_providers')) {
            $rows = DB::table('analytics_providers')->get();
            foreach ($rows as $row) {
                if (DB::table('integration_providers')->where('provider', $row->provider)->exists()) {
                    continue;
                }
                DB::table('integration_providers')->insert([
                    'provider' => $row->provider,
                    'enabled' => (bool) $row->enabled,
                    'credentials' => $row->credentials,
                    'status' => $row->status ?? 'idle',
                    'last_sync_at' => $row->last_sync_at,
                    'last_error' => $row->last_error,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
        }

        $now = now();
        foreach ([
            'brevo',
            'laravel_mail',
            'smartsupp',
            'jivo',
            'chatway',
            'google_analytics',
            'microsoft_clarity',
        ] as $provider) {
            if (! DB::table('integration_providers')->where('provider', $provider)->exists()) {
                DB::table('integration_providers')->insert([
                    'provider' => $provider,
                    'enabled' => false,
                    'status' => 'idle',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Migrate live-chat SystemSetting keys into integration_providers (dual-write era starts).
        if (Schema::hasTable('system_settings')) {
            $liveChat = DB::table('system_settings')->where('key', 'live_chat_provider')->value('value') ?: 'none';
            $smartsuppKey = DB::table('system_settings')->where('key', 'smartsupp_key')->value('value') ?: '';
            $jivoId = DB::table('system_settings')->where('key', 'jivo_widget_id')->value('value') ?: '';

            if ($smartsuppKey !== '') {
                DB::table('integration_providers')->where('provider', 'smartsupp')->update([
                    'enabled' => $liveChat === 'smartsupp',
                    'credentials' => Crypt::encryptString(json_encode(['key' => $smartsuppKey])),
                    'status' => $liveChat === 'smartsupp' ? 'connected' : 'idle',
                    'updated_at' => $now,
                ]);
            }
            if ($jivoId !== '') {
                DB::table('integration_providers')->where('provider', 'jivo')->update([
                    'enabled' => $liveChat === 'jivo',
                    'credentials' => Crypt::encryptString(json_encode(['widget_id' => $jivoId])),
                    'status' => $liveChat === 'jivo' ? 'connected' : 'idle',
                    'updated_at' => $now,
                ]);
            }
        }

        if (! Schema::hasTable('email_identities')) {
            Schema::create('email_identities', function (Blueprint $table) {
                $table->id();
                $table->string('profile', 40)->unique();
                $table->string('from_name');
                $table->string('from_email');
                $table->string('reply_to_email')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }

        $fromName = config('mail.from.name') ?: config('app.name', '7th Trade Hub');
        $fromEmail = config('mail.from.address') ?: 'noreply@example.com';
        $contactEmail = Schema::hasTable('system_settings')
            ? (DB::table('system_settings')->where('key', 'contact_email')->value('value') ?: '')
            : '';
        $contactAlt = Schema::hasTable('system_settings')
            ? (DB::table('system_settings')->where('key', 'contact_email_alt')->value('value') ?: '')
            : '';

        $identities = [
            ['profile' => 'general', 'from_name' => $fromName, 'from_email' => $contactAlt ?: $fromEmail, 'is_default' => true],
            ['profile' => 'support', 'from_name' => 'Support Team', 'from_email' => $contactEmail ?: $fromEmail, 'is_default' => false],
            ['profile' => 'sales', 'from_name' => 'Sales Team', 'from_email' => $fromEmail, 'is_default' => false],
            ['profile' => 'security', 'from_name' => 'Security', 'from_email' => $fromEmail, 'is_default' => false],
            ['profile' => 'billing', 'from_name' => 'Billing', 'from_email' => $fromEmail, 'is_default' => false],
            ['profile' => 'noreply', 'from_name' => $fromName, 'from_email' => $fromEmail, 'is_default' => false],
        ];

        foreach ($identities as $identity) {
            if (! DB::table('email_identities')->where('profile', $identity['profile'])->exists()) {
                DB::table('email_identities')->insert([
                    'profile' => $identity['profile'],
                    'from_name' => $identity['from_name'],
                    'from_email' => $identity['from_email'],
                    'reply_to_email' => null,
                    'is_default' => $identity['is_default'],
                    'enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (! Schema::hasTable('social_links')) {
            Schema::create('social_links', function (Blueprint $table) {
                $table->id();
                $table->string('platform', 60);
                $table->string('url');
                $table->string('icon', 60)->nullable();
                $table->unsignedBigInteger('icon_media_id')->nullable();
                $table->boolean('enabled')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['enabled', 'sort_order']);
            });
        }

        // Branding defaults (flat system_settings keys).
        if (Schema::hasTable('system_settings')) {
            $brandingDefaults = [
                'site_name' => config('app.name', '7th Trade Hub'),
                'site_short_name' => 'Trade Hub',
                'site_heading' => 'The Ultimate Digital Service Marketplace',
                'site_tagline' => 'Connecting markets, empowering traders.',
                'site_meta_description' => 'NGN wallet marketplace. Deposit, buy with escrow, sell digital products and services.',
                'favicon_media_id' => '',
                'logo_light_media_id' => '',
                'logo_dark_media_id' => '',
                'contact_address_street' => '',
                'contact_address_city' => '',
                'contact_address_state' => '',
                'contact_address_country' => '',
                'contact_address_postal' => '',
                'contact_latitude' => '',
                'contact_longitude' => '',
                'contact_maps_url' => '',
                'contact_maps_embed_url' => '',
                'contact_phone_support' => '',
                'contact_phone_general' => '',
                'contact_phone_whatsapp' => '',
                'contact_support_hours' => '',
                'contact_timezone' => 'Africa/Lagos',
                'contact_business_hours' => '',
                'contact_registration_number' => '',
                'contact_vat_number' => '',
                'contact_company_number' => '',
            ];

            // Migrate legacy contact phone into support phone if empty.
            $legacyPhone = DB::table('system_settings')->where('key', 'contact_phone')->value('value') ?: '';
            if ($legacyPhone !== '') {
                $brandingDefaults['contact_phone_support'] = $legacyPhone;
                $brandingDefaults['contact_phone_general'] = $legacyPhone;
            }

            foreach ($brandingDefaults as $key => $value) {
                if (! DB::table('system_settings')->where('key', $key)->exists()) {
                    DB::table('system_settings')->insert([
                        'key' => $key,
                        'value' => (string) $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('email_identities');
        Schema::dropIfExists('integration_providers');
    }
};
