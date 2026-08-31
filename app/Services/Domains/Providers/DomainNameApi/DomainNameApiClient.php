<?php

namespace App\Services\Domains\Providers\DomainNameApi;

use App\Models\DomainProvider;
use App\Services\Domains\Exceptions\DomainBusinessException;
use App\Services\Domains\Http\SendsDomainProviderRequests;
use RuntimeException;

class DomainNameApiClient
{
    use SendsDomainProviderRequests;

    public function baseUrl(DomainProvider $provider): string
    {
        return $provider->sandbox
            ? 'https://ote.domainresellerapi.com'
            : 'https://api.domainresellerapi.com';
    }

    /**
     * @return array<string, mixed>
     */
    public function listTlds(DomainProvider $provider, int $skip = 0, int $max = 100): array
    {
        return $this->request($provider, 'GET', '/api/v1/products/tlds', [
            'SkipCount' => $skip,
            'MaxResultCount' => $max,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function searchDomain(DomainProvider $provider, string $fqdn): array
    {
        return $this->request($provider, 'POST', '/api/v1/domains/search', [], [
            'domainName' => $fqdn,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function registerWithContacts(DomainProvider $provider, array $payload): array
    {
        return $this->request($provider, 'POST', '/api/v1/domains/register-with-contacts', [], $payload);
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>|null  $json
     * @return array<string, mixed>
     */
    private function request(DomainProvider $provider, string $method, string $path, array $query = [], ?array $json = null): array
    {
        $credentials = $provider->credentials ?? [];
        $resellerId = (string) ($credentials['reseller_id'] ?? '');
        $apiKey = (string) ($credentials['api_key'] ?? '');

        if ($resellerId === '' || $apiKey === '') {
            throw new DomainBusinessException('Domain provider credentials are not configured.');
        }

        $pending = $this->domainHttpPending($provider, [
            '__reseller' => $resellerId,
            'X-API-KEY' => $apiKey,
        ]);

        $url = rtrim($this->baseUrl($provider), '/').$path;

        return $this->sendDomainProviderRequest($pending, $method, $url, $query, $json);
    }
}
