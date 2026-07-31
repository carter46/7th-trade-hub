<?php

namespace App\Models;

/**
 * Dual-read façade: analytics call sites keep using AnalyticsProvider
 * while data lives in integration_providers.
 */
class AnalyticsProvider extends IntegrationProvider
{
    public const PROVIDER_GOOGLE_ANALYTICS = self::GOOGLE_ANALYTICS;

    public const PROVIDER_MICROSOFT_CLARITY = self::MICROSOFT_CLARITY;

    protected $table = 'integration_providers';
}
