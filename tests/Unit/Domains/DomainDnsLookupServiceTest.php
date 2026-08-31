<?php

namespace Tests\Unit\Domains;

use App\Services\Domains\DomainDnsLookupService;
use Tests\TestCase;

class DomainDnsLookupServiceTest extends TestCase
{
    public function test_normalize_list_strips_case_and_trailing_dots(): void
    {
        $service = new DomainDnsLookupService;

        $this->assertSame(
            ['ns1.example.com', 'ns2.example.com'],
            $service->normalizeList(['NS1.Example.com.', 'ns2.example.com', 'ns1.example.com']),
        );
    }

    public function test_matches_platform_defaults_requires_all_required_hosts(): void
    {
        config(['domains.default_nameservers' => ['ns1.platform.test', 'ns2.platform.test']]);
        $service = new DomainDnsLookupService;

        $this->assertTrue($service->matchesPlatformDefaults([
            'ns2.platform.test',
            'ns1.platform.test',
            'ns3.extra.test',
        ]));

        $this->assertFalse($service->matchesPlatformDefaults([
            'ns1.platform.test',
        ]));
    }

    public function test_lookup_uses_injected_resolver(): void
    {
        $service = new DomainDnsLookupService(function (string $fqdn) {
            $this->assertSame('example.com', $fqdn);

            return [
                ['target' => 'NS1.Current.HOST.'],
                ['target' => 'ns2.current.host'],
            ];
        });

        $result = $service->lookup('example.com');

        $this->assertTrue($result['registered']);
        $this->assertSame('active', $result['status']);
        $this->assertSame(['ns1.current.host', 'ns2.current.host'], $result['nameservers']);
    }

    public function test_lookup_empty_ns_is_not_registered(): void
    {
        $service = new DomainDnsLookupService(fn () => []);

        $result = $service->lookup('missing.com');

        $this->assertFalse($result['registered']);
        $this->assertSame('not_found', $result['status']);
        $this->assertSame([], $result['nameservers']);
    }
}
