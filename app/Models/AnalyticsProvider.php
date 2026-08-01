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

    public const PROVIDER_GOOGLE_TAG_MANAGER = self::GOOGLE_TAG_MANAGER;

    public const PROVIDER_META_PIXEL = self::META_PIXEL;

    protected $table = 'integration_providers';
}
