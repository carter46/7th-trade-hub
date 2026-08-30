<?php

namespace App\Services\SiteIntegrations;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Central Hub → merchant HTTP client with SSRF protections.
 */
class IntegrationHttpClient
{
    public function __construct(
        private IntegrationOutboundUrlGuard $urlGuard,
    ) {}

    /**
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>  $body
     *
     * @throws InvalidArgumentException
     */
    public function postJson(string $url, array $headers, array $body, int $timeoutSeconds = 15): Response
    {
        $safeUrl = $this->urlGuard->assertSafe($url, httpsOnly: true);

        return Http::timeout($timeoutSeconds)
            ->connectTimeout(5)
            ->withoutRedirecting()
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->post($safeUrl, $body);
    }
}
