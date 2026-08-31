<?php

namespace App\Services\Domains\Providers\NameCom;

use App\Models\DomainProvider;
use App\Services\Domains\Exceptions\DomainBusinessException;
use App\Services\Domains\Http\SendsDomainProviderRequests;
use RuntimeException;

class NameComClient
{
    use SendsDomainProviderRequests;

    public function baseUrl(DomainProvider $provider): string
    {
        return $provider->sandbox
            ? 'https://api.dev.name.com'
            : 'https://api.name.com';
    }

    /**
     * @return array<string, mixed>
     */
    public function hello(DomainProvider $provider): array
    {
        return $this->request($provider, 'GET', '/core/v1/hello');
    }

    /**
     * @return array<string, mixed>
     */
    public function tldPricing(DomainProvider $provider, int $page = 1, int $perPage = 100): array
    {
        return $this->request($provider, 'GET', '/core/v1/tldpricing', [
            'duration' => 1,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function checkAvailability(DomainProvider $provider, string $fqdn): array
    {
        return $this->request($provider, 'POST', '/core/v1/domains:checkAvailability', [], [
            'domainNames' => [$fqdn],
            'purchaseType' => 'registration',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPricing(DomainProvider $provider, string $fqdn, int $years = 1): array
    {
        $encoded = rawurlencode($fqdn);

        return $this->request($provider, 'GET', "/core/v1/domains/{$encoded}:getPricing", [
            'years' => $years,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function createDomain(DomainProvider $provider, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->request($provider, 'POST', '/core/v1/domains', [], $payload, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>|null  $json
     * @return array<string, mixed>
     */
    private function request(DomainProvider $provider, string $method, string $path, array $query = [], ?array $json = null, ?string $idempotencyKey = null): array
    {
        $credentials = $provider->credentials ?? [];
        $username = (string) ($credentials['username'] ?? '');
        $token = (string) ($credentials['api_token'] ?? '');

        if ($username === '' || $token === '') {
            throw new DomainBusinessException('Domain provider credentials are not configured.');
        }

        $headers = [];
        if ($idempotencyKey) {
            $headers['X-Idempotency-Key'] = $idempotencyKey;
        }

        $pending = $this->domainHttpPending($provider, $headers)
            ->withBasicAuth($username, $token);

        $url = rtrim($this->baseUrl($provider), '/').$path;

        return $this->sendDomainProviderRequest($pending, $method, $url, $query, $json);
    }
}
