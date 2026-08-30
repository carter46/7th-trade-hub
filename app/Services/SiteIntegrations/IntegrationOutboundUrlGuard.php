<?php

namespace App\Services\SiteIntegrations;

use InvalidArgumentException;

/**
 * SSRF guard for Hub → merchant HTTP destinations.
 */
class IntegrationOutboundUrlGuard
{
    /**
     * @throws InvalidArgumentException
     */
    public function assertSafe(string $url, bool $httpsOnly = true): string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Outbound URL is required.');
        }

        $parts = parse_url($trimmed);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            throw new InvalidArgumentException('Outbound URL is invalid.');
        }

        $scheme = strtolower((string) $parts['scheme']);
        if ($httpsOnly) {
            if ($scheme !== 'https') {
                throw new InvalidArgumentException('Outbound URL must use HTTPS.');
            }
        } elseif (! in_array($scheme, ['https', 'http'], true)) {
            throw new InvalidArgumentException('Outbound URL scheme is not allowed.');
        }

        $host = strtolower((string) $parts['host']);
        $host = trim($host, '[]');

        if ($host === 'localhost' || str_ends_with($host, '.localhost') || $host === '0.0.0.0') {
            throw new InvalidArgumentException('Outbound URL host is not allowed.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->assertPublicIp($host);
        } elseif (! app()->runningUnitTests()) {
            $resolved = @gethostbynamel($host);
            if ($resolved === false || $resolved === []) {
                $ipv4 = @gethostbyname($host);
                if ($ipv4 === $host || $ipv4 === '' || $ipv4 === false) {
                    throw new InvalidArgumentException('Outbound URL host could not be resolved.');
                }
                $this->assertPublicIp($ipv4);
            } else {
                foreach ($resolved as $ip) {
                    $this->assertPublicIp($ip);
                }
            }
        }

        return $trimmed;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertPublicIp(string $ip): void
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Outbound URL resolved to an invalid IP.');
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            if ($long === false) {
                throw new InvalidArgumentException('Outbound URL resolved to an invalid IPv4 address.');
            }

            $blocked = [
                ['0.0.0.0', '0.255.255.255'],
                ['10.0.0.0', '10.255.255.255'],
                ['127.0.0.0', '127.255.255.255'],
                ['169.254.0.0', '169.254.255.255'],
                ['172.16.0.0', '172.31.255.255'],
                ['192.168.0.0', '192.168.255.255'],
                ['100.64.0.0', '100.127.255.255'],
                ['192.0.0.0', '192.0.0.255'],
                ['192.0.2.0', '192.0.2.255'],
                ['198.18.0.0', '198.19.255.255'],
                ['198.51.100.0', '198.51.100.255'],
                ['203.0.113.0', '203.0.113.255'],
                ['224.0.0.0', '255.255.255.255'],
            ];

            foreach ($blocked as [$start, $end]) {
                if ($long >= ip2long($start) && $long <= ip2long($end)) {
                    throw new InvalidArgumentException('Outbound URL resolves to a private or reserved address.');
                }
            }

            return;
        }

        // IPv6
        $normalized = strtolower($ip);
        if ($normalized === '::1' || str_starts_with($normalized, 'fc') || str_starts_with($normalized, 'fd')
            || str_starts_with($normalized, 'fe80') || $normalized === '::') {
            throw new InvalidArgumentException('Outbound URL resolves to a private or reserved IPv6 address.');
        }

        // IPv4-mapped IPv6
        if (str_starts_with($normalized, '::ffff:')) {
            $mapped = substr($normalized, 7);
            if (filter_var($mapped, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $this->assertPublicIp($mapped);
            }
        }
    }
}
