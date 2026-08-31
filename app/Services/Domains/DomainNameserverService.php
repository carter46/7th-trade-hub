<?php

namespace App\Services\Domains;

use App\Models\DomainRegistration;
use App\Models\User;
use App\Services\Domains\Exceptions\DomainBusinessException;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DomainNameserverService
{
    public function __construct(
        private DomainProviderManager $providers,
        private DomainAuditLogger $audit,
    ) {}

    /**
     * Platform default nameservers for new registrations only.
     *
     * @return list<string>
     */
    public function defaultNameservers(): array
    {
        return array_values(array_filter(
            config('domains.default_nameservers', []),
            fn (mixed $ns) => is_string($ns) && trim($ns) !== '',
        ));
    }

    /**
     * @param  array<int, mixed>  $input
     * @return list<string>
     */
    public function validateNameservers(array $input): array
    {
        $normalized = [];

        foreach ($input as $value) {
            if (! is_string($value)) {
                continue;
            }

            $host = strtolower(rtrim(trim($value), '.'));
            if ($host === '') {
                continue;
            }

            if (! $this->isValidNameserverHost($host)) {
                throw new InvalidArgumentException('Enter valid nameserver hostnames (e.g. ns1.example.com).');
            }

            if (! in_array($host, $normalized, true)) {
                $normalized[] = $host;
            }
        }

        if (count($normalized) < 2) {
            throw new InvalidArgumentException('Enter at least two nameservers.');
        }

        if (count($normalized) > 4) {
            throw new InvalidArgumentException('You can enter up to four nameservers.');
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    public function syncFromProvider(DomainRegistration $registration): array
    {
        $this->assertRegistered($registration);

        $provider = $this->providers->providerRecord($registration->provider_key);
        $adapter = $this->providers->adapterFor($provider);

        $nameservers = $adapter->getNameservers($provider, $registration->fqdn);

        $registration->update([
            'nameservers' => $nameservers,
            'nameservers_synced_at' => now(),
        ]);

        $this->audit->log('domains.nameservers.synced', $registration->fresh(), [
            'fqdn' => $registration->fqdn,
        ], auth()->id());

        return $nameservers;
    }

    /**
     * @param  array<int, mixed>  $input
     * @return list<string>
     */
    public function updateForCustomer(DomainRegistration $registration, array $input, User $actor): array
    {
        $this->assertRegistered($registration);

        $nameservers = $this->validateNameservers($input);

        return $this->pushToProvider($registration, $nameservers, $actor, 'domains.nameservers.updated');
    }

    /**
     * @return list<string>
     */
    public function applyPlatformDefaults(DomainRegistration $registration, User $actor): array
    {
        $this->assertRegistered($registration);

        $defaults = $this->defaultNameservers();
        if ($defaults === []) {
            throw new DomainBusinessException('Platform default nameservers are not configured.');
        }

        return $this->pushToProvider($registration, $defaults, $actor, 'domains.nameservers.defaults_applied');
    }

    /**
     * Resolve confirmed nameservers after successful registration.
     *
     * @param  array<string, mixed>  $providerMeta
     * @return list<string>
     */
    public function resolveAfterRegistration(string $providerKey, string $fqdn, array $providerMeta = []): array
    {
        $fromMeta = $this->extractFromProviderMeta($providerMeta);
        if ($fromMeta !== []) {
            return $fromMeta;
        }

        try {
            $provider = $this->providers->providerRecord($providerKey);
            $adapter = $this->providers->adapterFor($provider);

            return $adapter->getNameservers($provider, $fqdn);
        } catch (\Throwable) {
            return $this->defaultNameservers();
        }
    }

    /**
     * @param  list<string>  $nameservers
     * @return list<string>
     */
    private function pushToProvider(DomainRegistration $registration, array $nameservers, User $actor, string $auditAction): array
    {
        $provider = $this->providers->providerRecord($registration->provider_key);
        $adapter = $this->providers->adapterFor($provider);

        try {
            $adapter->updateNameservers($provider, $registration->fqdn, $nameservers);
        } catch (\Throwable $e) {
            $this->audit->log('domains.nameservers.update_failed', $registration, [
                'fqdn' => $registration->fqdn,
                'message' => Str::limit($e->getMessage(), 200),
            ], $actor->id);

            throw $e;
        }

        $registration->update([
            'nameservers' => $nameservers,
            'nameservers_updated_at' => now(),
            'nameservers_synced_at' => now(),
        ]);

        $this->audit->log($auditAction, $registration->fresh(), [
            'fqdn' => $registration->fqdn,
        ], $actor->id);

        return $nameservers;
    }

    private function assertRegistered(DomainRegistration $registration): void
    {
        if ($registration->status !== DomainRegistration::STATUS_REGISTERED) {
            throw new DomainBusinessException('Nameservers can only be changed for registered domains.');
        }
    }

    private function isValidNameserverHost(string $host): bool
    {
        if (strlen($host) > 253) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', $host);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<string>
     */
    private function extractFromProviderMeta(array $meta): array
    {
        $candidates = [
            $meta['domain']['nameservers'] ?? null,
            $meta['data']['nameServers'] ?? null,
            $meta['nameServers'] ?? null,
            $meta['nameservers'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $hosts = [];
            foreach ($candidate as $entry) {
                if (is_string($entry) && trim($entry) !== '') {
                    $hosts[] = strtolower(rtrim(trim($entry), '.'));
                } elseif (is_array($entry)) {
                    $host = (string) ($entry['hostname'] ?? $entry['name'] ?? $entry['host'] ?? '');
                    if ($host !== '') {
                        $hosts[] = strtolower(rtrim(trim($host), '.'));
                    }
                }
            }

            if (count($hosts) >= 2) {
                return array_values(array_unique($hosts));
            }
        }

        return [];
    }
}
