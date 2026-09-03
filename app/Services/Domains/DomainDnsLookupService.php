<?php

namespace App\Services\Domains;

use App\Support\Domains\DomainFqdn;
use InvalidArgumentException;

class DomainDnsLookupService
{
    /**
     * @param  (callable(string): list<array<string, mixed>>|false)|null  $resolver
     */
    public function __construct(
        private $resolver = null,
    ) {}

    /**
     * @return array{fqdn: string, registered: bool, status: string, nameservers: list<string>}
     */
    public function lookup(string $input): array
    {
        // Connect-existing may use subdomains / multi-label hosts; registration keeps apex-only elsewhere.
        $parsed = DomainFqdn::fromFqdn($input, apexOnly: false);
        $fqdn = $parsed['fqdn'];
        $nameservers = $this->fetchNameservers($fqdn);

        if ($nameservers === []) {
            return [
                'fqdn' => $fqdn,
                'registered' => false,
                'status' => 'not_found',
                'nameservers' => [],
            ];
        }

        return [
            'fqdn' => $fqdn,
            'registered' => true,
            'status' => 'active',
            'nameservers' => $nameservers,
        ];
    }

    /**
     * @param  list<string>  $detected
     * @param  list<string>|null  $required
     */
    public function matchesPlatformDefaults(array $detected, ?array $required = null): bool
    {
        $required = $required ?? $this->platformNameservers();
        if ($required === []) {
            return false;
        }

        $detectedSet = array_fill_keys($this->normalizeList($detected), true);

        foreach ($this->normalizeList($required) as $host) {
            if (! isset($detectedSet[$host])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    public function platformNameservers(): array
    {
        return $this->normalizeList(config('domains.default_nameservers', []));
    }

    public function assertPlatformNameserversConfigured(): void
    {
        if (count($this->platformNameservers()) < 2) {
            throw new InvalidArgumentException('Platform nameservers are not configured. Contact support.');
        }
    }

    /**
     * @return list<string>
     */
    private function fetchNameservers(string $fqdn): array
    {
        $resolver = $this->resolver ?? function (string $host): array|false {
            return @dns_get_record($host, DNS_NS);
        };

        try {
            $records = $resolver($fqdn);
        } catch (\Throwable) {
            return [];
        }

        if (! is_array($records) || $records === []) {
            return [];
        }

        $hosts = [];
        foreach ($records as $row) {
            if (! is_array($row)) {
                continue;
            }
            $target = $row['target'] ?? $row['ns'] ?? null;
            if (is_string($target) && trim($target) !== '') {
                $hosts[] = $target;
            }
        }

        return $this->normalizeList($hosts);
    }

    /**
     * @param  list<mixed>|array<int, mixed>  $hosts
     * @return list<string>
     */
    public function normalizeList(array $hosts): array
    {
        $normalized = [];

        foreach ($hosts as $host) {
            if (! is_string($host)) {
                continue;
            }

            $value = strtolower(rtrim(trim($host), '.'));
            if ($value === '' || isset($normalized[$value])) {
                continue;
            }

            $normalized[$value] = $value;
        }

        return array_values($normalized);
    }
}
