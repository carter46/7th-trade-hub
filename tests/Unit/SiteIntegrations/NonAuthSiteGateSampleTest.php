<?php

namespace Tests\Unit\SiteIntegrations;

use PHPUnit\Framework\TestCase;

/**
 * Contract tests for the merchant sample gate (monotonic apply + offline semantics).
 */
class NonAuthSiteGateSampleTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 3).'/docs/integrations/samples/php/non-auth-site-gate.php';
    }

    public function test_stale_active_does_not_overwrite_newer_expired(): void
    {
        $current = [
            'status' => 'expired',
            'expires_at' => '2026-09-18T12:00:00+00:00',
            'updated_at' => '2026-09-18T12:00:00+00:00',
        ];

        $staleActive = [
            'status' => 'active',
            'expires_at' => '2026-12-01T00:00:00+00:00',
            'updated_at' => '2026-09-18T11:00:00+00:00',
        ];

        $this->assertFalse(seventh_tradehub_should_apply_subscription($staleActive, $current));
    }

    public function test_newer_active_applies_over_expired(): void
    {
        $current = [
            'status' => 'expired',
            'expires_at' => '2026-09-18T12:00:00+00:00',
            'updated_at' => '2026-09-18T12:00:00+00:00',
        ];

        $newerActive = [
            'status' => 'active',
            'expires_at' => '2026-12-01T00:00:00+00:00',
            'updated_at' => '2026-09-18T13:00:00+00:00',
        ];

        $this->assertTrue(seventh_tradehub_should_apply_subscription($newerActive, $current));
    }

    public function test_pending_setup_and_unknown_are_offline(): void
    {
        $fresh = gmdate('c');

        $this->assertTrue(seventh_tradehub_is_offline([
            'status' => 'pending_setup',
            'last_synced_at' => $fresh,
            'expires_at' => gmdate('c', time() + 86400),
        ]));

        $this->assertTrue(seventh_tradehub_is_offline([
            'status' => 'weird_status',
            'last_synced_at' => $fresh,
            'expires_at' => gmdate('c', time() + 86400),
        ]));

        $this->assertFalse(seventh_tradehub_is_offline([
            'status' => 'active',
            'last_synced_at' => $fresh,
            'expires_at' => gmdate('c', time() + 86400),
        ]));
    }

    public function test_active_without_timestamps_cannot_resurrect_offline(): void
    {
        $current = [
            'status' => 'suspended',
            'expires_at' => '2026-09-18T12:00:00+00:00',
            'updated_at' => '2026-09-18T12:00:00+00:00',
        ];

        $ambiguousActive = [
            'status' => 'active',
            'expires_at' => '2026-09-18T12:00:00+00:00',
        ];

        $this->assertFalse(seventh_tradehub_should_apply_subscription($ambiguousActive, $current));
    }
}
