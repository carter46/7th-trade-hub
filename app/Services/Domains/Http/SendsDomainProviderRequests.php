<?php

namespace App\Services\Domains\Http;

use App\Models\DomainProvider;
use App\Services\Domains\Exceptions\DomainProviderAuthException;
use App\Services\Domains\Exceptions\DomainProviderTransportException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

trait SendsDomainProviderRequests
{
    protected function domainHttpPending(DomainProvider $provider, array $headers = []): PendingRequest
    {
        $timeout = max(5, (int) config('domains.http_timeout_seconds', 15));

        return Http::timeout($timeout)
            ->connectTimeout(min(10, (int) config('domains.http_connect_timeout_seconds', 10)))
            ->withOptions(['allow_redirects' => false])
            ->acceptJson()
            ->withHeaders($headers);
    }

    /**
     * @return array<string, mixed>
     */
    protected function sendDomainProviderRequest(
        PendingRequest $pending,
        string $method,
        string $url,
        array $query = [],
        ?array $json = null,
    ): array {
        try {
            $response = match (strtoupper($method)) {
                'GET' => $pending->get($url, $query),
                'POST' => $pending->post($url, $json ?? []),
                'PUT' => $pending->put($url, $json ?? []),
                default => throw new RuntimeException('Unsupported HTTP method.'),
            };

            if ($response->status() === 401 || $response->status() === 403) {
                throw new DomainProviderAuthException('Domain provider authentication failed.');
            }

            if ($response->serverError()) {
                throw new DomainProviderTransportException('Domain provider is temporarily unavailable.');
            }

            $response->throw();

            $body = $response->json();
            if (! is_array($body)) {
                throw new DomainProviderTransportException('Domain provider returned an invalid response.');
            }

            $maxBytes = max(65536, (int) config('domains.http_max_response_bytes', 1048576));
            $encoded = json_encode($body);
            if ($encoded !== false && strlen($encoded) > $maxBytes) {
                throw new DomainProviderTransportException('Domain provider response was too large.');
            }

            /** @var array<string, mixed> $body */
            return $body;
        } catch (ConnectionException $e) {
            throw new DomainProviderTransportException('Unable to reach domain provider.', 0, $e);
        } catch (DomainProviderAuthException|DomainProviderTransportException $e) {
            throw $e;
        } catch (RequestException $e) {
            throw new DomainProviderTransportException('Domain provider request failed.', 0, $e);
        }
    }
}
