<?php

namespace Tests\Unit\SiteIntegrations;

use App\Services\SiteIntegrations\IntegrationOutboundUrlGuard;
use InvalidArgumentException;
use Tests\TestCase;

class IntegrationOutboundUrlGuardTest extends TestCase
{
    private IntegrationOutboundUrlGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guard = new IntegrationOutboundUrlGuard;
    }

    public function test_rejects_http_scheme(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->guard->assertSafe('http://example.com/health');
    }

    public function test_rejects_localhost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->guard->assertSafe('https://localhost/health');
    }

    public function test_rejects_loopback_ip(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->guard->assertSafe('https://127.0.0.1/health');
    }

    public function test_rejects_private_ipv4(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->guard->assertSafe('https://10.0.0.5/health');
    }

    public function test_rejects_link_local(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->guard->assertSafe('https://169.254.1.1/health');
    }

    public function test_rejects_metadata_ip(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->guard->assertSafe('https://169.254.169.254/latest/meta-data');
    }

    public function test_allows_https_public_hostname_in_tests(): void
    {
        $url = $this->guard->assertSafe('https://customer.example.com/api/health');
        $this->assertSame('https://customer.example.com/api/health', $url);
    }
}
