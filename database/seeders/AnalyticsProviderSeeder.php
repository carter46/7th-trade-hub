<?php

namespace Database\Seeders;

use App\Models\AnalyticsProvider;
use Illuminate\Database\Seeder;

class AnalyticsProviderSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            AnalyticsProvider::PROVIDER_GOOGLE_ANALYTICS,
            AnalyticsProvider::PROVIDER_MICROSOFT_CLARITY,
        ] as $provider) {
            AnalyticsProvider::query()->firstOrCreate(
                ['provider' => $provider],
                ['enabled' => false, 'status' => 'idle']
            );
        }
    }
}
