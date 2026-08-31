<?php

namespace App\Services\Domains;

use App\Contracts\Domains\DomainProviderInterface;
use App\Data\Domains\DomainTld;
use App\Models\DomainProvider;
use App\Services\Domains\Exceptions\DomainBusinessException;
use App\Services\Domains\Exceptions\DomainProviderTransportException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use RuntimeException;

class DomainProviderManager
{
    private const TLD_REGISTRY_CACHE_KEY = 'domain.tlds.registry';

    /**
     * @param  callable(DomainProviderInterface, DomainProvider): mixed  $callback
     */
    public function attempt(callable $callback): mixed
    {
        $providers = $this->orderedEnabledProviders();

        if ($providers->isEmpty()) {
            throw new RuntimeException('Domain search is temporarily unavailable.');
        }

        $last = null;

        foreach ($providers as $provider) {
            try {
                $adapter = $this->adapterFor($provider);

                return $callback($adapter, $provider);
            } catch (DomainBusinessException $e) {
                throw $e;
            } catch (DomainProviderTransportException $e) {
                $last = $e;
                $provider->update(['health_status' => DomainProvider::HEALTH_UNAVAILABLE]);
            } catch (\Throwable $e) {
                throw $e;
            }
        }

        throw $last ?? new RuntimeException('Domain search is temporarily unavailable.');
    }

    /**
     * @return array{available: bool, provider?: DomainProvider, registration?: mixed, availability?: mixed}
     */
    public function quoteThroughTld(string $tld, string $fqdn, callable $quoteFn): array
    {
        $registry = $this->tldRegistry();
        $entry = $registry[$tld] ?? null;

        if (! $entry instanceof DomainTld) {
            throw new DomainBusinessException('Selected extension is not supported.');
        }

        $primary = $this->providerRecord($entry->primaryProviderKey);
        $this->requireEnabledForCheckout($primary);

        $providersToTry = collect([$primary])
            ->merge($this->fallbackProvidersForTld($tld, $primary))
            ->unique('id')
            ->values();

        $lastTransport = null;

        foreach ($providersToTry as $provider) {
            try {
                $this->requireEnabledForCheckout($provider);
                $adapter = $this->adapterFor($provider);
                $result = $quoteFn($adapter, $provider);

                if (is_array($result)) {
                    if ($result['available'] ?? false) {
                        $provider->update([
                            'health_status' => DomainProvider::HEALTH_HEALTHY,
                            'last_health_check_at' => now(),
                        ]);
                    }

                    return $result;
                }

                return ['available' => false, 'availability' => null];
            } catch (DomainBusinessException $e) {
                throw $e;
            } catch (DomainProviderTransportException $e) {
                $lastTransport = $e;
                $provider->update(['health_status' => DomainProvider::HEALTH_UNAVAILABLE]);
            }
        }

        if ($lastTransport) {
            throw $lastTransport;
        }

        throw new RuntimeException('Domain search is temporarily unavailable.');
    }

    public function resolvePrimaryProviderForTld(string $tld): DomainProvider
    {
        $registry = $this->tldRegistry();
        $entry = $registry[$tld] ?? null;

        if (! $entry instanceof DomainTld) {
            throw new DomainBusinessException('Selected extension is not supported.');
        }

        return $this->providerRecord($entry->primaryProviderKey);
    }

    /**
     * @return list<DomainProvider>
     */
    public function fallbackProvidersForTld(string $tld, DomainProvider $exclude): array
    {
        $registry = $this->tldRegistry();
        $entry = $registry[$tld] ?? null;

        if (! $entry instanceof DomainTld) {
            return [];
        }

        $keys = array_values(array_filter(
            $entry->supportedProviderKeys,
            fn (string $key) => $key !== $exclude->key,
        ));

        if ($keys === []) {
            return [];
        }

        $enabled = $this->orderedEnabledProviders()->keyBy('key');

        $fallbacks = [];
        foreach ($keys as $key) {
            $provider = $enabled->get($key);
            if ($provider instanceof DomainProvider) {
                $fallbacks[] = $provider;
            }
        }

        return $fallbacks;
    }

    /**
     * @return array<string, DomainTld>
     */
    public function tldRegistry(): array
    {
        $ttl = max(1, (int) config('domains.tld_cache_ttl_minutes', 60));

        /** @var array<string, DomainTld> */
        return Cache::remember(self::TLD_REGISTRY_CACHE_KEY, now()->addMinutes($ttl), function () {
            $list = $this->mergedTldList();
            $registry = [];
            foreach ($list as $row) {
                $registry[$row->tld] = $row;
            }

            return $registry;
        });
    }

    public static function forgetTldCaches(): void
    {
        Cache::forget(self::TLD_REGISTRY_CACHE_KEY);
        Cache::forget('domain.tlds.merged');
    }

    /**
     * @return Collection<int, DomainProvider>
     */
    public function orderedEnabledProviders(): Collection
    {
        $enabled = DomainProvider::query()
            ->where('enabled', true)
            ->get();

        if ($enabled->isEmpty()) {
            return collect();
        }

        $default = $enabled->firstWhere('is_default', true);
        $fallbacks = $enabled
            ->reject(fn (DomainProvider $p) => $default && $p->is($default))
            ->sortBy(fn (DomainProvider $p) => $p->fallback_priority ?? 999);

        return collect([$default])->filter()->merge($fallbacks)->values();
    }

    public function adapterFor(DomainProvider $provider): DomainProviderInterface
    {
        $class = $provider->adapter_class;

        if (! class_exists($class)) {
            throw new InvalidArgumentException('Domain provider adapter is missing.');
        }

        $adapter = app($class);

        if (! $adapter instanceof DomainProviderInterface) {
            throw new InvalidArgumentException('Domain provider adapter is invalid.');
        }

        return $adapter;
    }

    public function adapterForKey(string $key): DomainProviderInterface
    {
        $provider = $this->providerRecord($key);

        return $this->adapterFor($provider);
    }

    /**
     * @return list<DomainTld>
     */
    public function mergedTldList(): array
    {
        $enabled = $this->orderedEnabledProviders();
        $defaultKey = $enabled->firstWhere('is_default', true)?->key;

        /** @var array<string, array{providers: array<string, DomainTld>}> $byTld */
        $byTld = [];

        foreach ($enabled as $provider) {
            try {
                $adapter = $this->adapterFor($provider);
                $tlds = $adapter->listTlds($provider);

                foreach ($tlds as $row) {
                    $tld = $row->tld;
                    if (! isset($byTld[$tld])) {
                        $byTld[$tld] = ['providers' => []];
                    }
                    $byTld[$tld]['providers'][$provider->key] = new DomainTld(
                        tld: $tld,
                        primaryProviderKey: $provider->key,
                        supportedProviderKeys: [$provider->key],
                        registrationCost: $row->registrationCost,
                        currency: $row->currency,
                        purchasable: $row->purchasable,
                    );
                }

                $provider->update([
                    'health_status' => DomainProvider::HEALTH_HEALTHY,
                    'last_health_check_at' => now(),
                ]);
            } catch (\Throwable) {
                $provider->update(['health_status' => DomainProvider::HEALTH_UNAVAILABLE]);
            }
        }

        if ($byTld === []) {
            throw new RuntimeException('Domain search is temporarily unavailable.');
        }

        $merged = [];
        foreach ($byTld as $tld => $data) {
            $providers = $data['providers'];
            $supportedKeys = array_keys($providers);

            $primaryKey = ($defaultKey && isset($providers[$defaultKey]))
                ? $defaultKey
                : $this->cheapestProviderKey($providers);

            $primary = $providers[$primaryKey];

            $merged[$tld] = new DomainTld(
                tld: $tld,
                primaryProviderKey: $primaryKey,
                supportedProviderKeys: $supportedKeys,
                registrationCost: $primary->registrationCost,
                currency: $primary->currency,
                purchasable: $primary->purchasable,
            );
        }

        ksort($merged);

        return array_values($merged);
    }

    /**
     * @param  array<string, DomainTld>  $providers
     */
    private function cheapestProviderKey(array $providers): string
    {
        $best = null;
        $bestKey = array_key_first($providers);

        foreach ($providers as $key => $row) {
            $cost = $row->registrationCost ?? PHP_FLOAT_MAX;
            if ($best === null || $cost < $best) {
                $best = $cost;
                $bestKey = $key;
            }
        }

        return (string) $bestKey;
    }

    public function providerRecord(string $key, bool $requireEnabled = false): DomainProvider
    {
        $provider = DomainProvider::query()->where('key', $key)->firstOrFail();

        if ($requireEnabled) {
            $this->requireEnabledForCheckout($provider);
        }

        return $provider;
    }

    public function requireEnabledForCheckout(DomainProvider $provider): void
    {
        if (! $provider->enabled) {
            throw new DomainBusinessException('Domain search is temporarily unavailable. Please check availability again.');
        }
    }
}
