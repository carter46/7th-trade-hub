<?php

namespace Tests\Unit\Domains;

use App\Support\Domains\DomainFqdn;
use Tests\TestCase;

class DomainFqdnValidateLabelTest extends TestCase
{
    public function test_rejects_spaces_in_real_time_sanitization(): void
    {
        $result = DomainFqdn::validateLabel('my site');

        $this->assertSame('mysite', $result['value']);
        $this->assertStringContainsString('Spaces', (string) $result['error']);
    }

    public function test_strips_extension_from_label_and_detects_tld(): void
    {
        $result = DomainFqdn::validateLabel('ulopamy.com');

        $this->assertSame('ulopamy', $result['value']);
        $this->assertSame('com', $result['detected_tld']);
        $this->assertStringContainsString('extension', strtolower((string) $result['error']));
    }

    public function test_valid_label_has_no_error(): void
    {
        $result = DomainFqdn::validateLabel('mysite');

        $this->assertSame('mysite', $result['value']);
        $this->assertNull($result['error']);
    }
}
