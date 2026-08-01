<?php

namespace App\Modules\Wallet\Payments\Monnify;

use App\Models\IntegrationProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MonnifyClient
{
    private ?IntegrationProvider $provider = null;

    public function provider(): IntegrationProvider
    {
        return $this->provider ??= IntegrationProvider::forProvider(IntegrationProvider::MONNIFY);
    }

    public function isConfigured(): bool
    {
        $p = $this->provider();

        return $p->enabled
            && filled($p->credential('api_key'))
            && filled($p->credential('secret_key'))
            && filled($p->credential('contract_code'));
    }

    public function baseUrl(): string
    {
        $env = (string) ($this->provider()->meta['environment'] ?? 'sandbox');

        return $env === 'live'
            ? 'https://api.monnify.com'
            : 'https://sandbox.monnify.com';
    }

    public function contractCode(): string
    {
        return (string) $this->provider()->credential('contract_code', '');
    }

    public function walletAccountNumber(): string
    {
        return (string) $this->provider()->credential('wallet_account_number', '');
    }

    public function secretKey(): string
    {
        return (string) $this->provider()->credential('secret_key', '');
    }

    public function apiKey(): string
    {
        return (string) $this->provider()->credential('api_key', '');
    }

    public function accessToken(): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Monnify is not configured in Admin Settings.');
        }

        $cacheKey = 'monnify_access_token_'.$this->provider()->id;

        return Cache::remember($cacheKey, now()->addMinutes(50), function () {
            $auth = base64_encode($this->apiKey().':'.$this->secretKey());
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$auth,
            ])->post($this->baseUrl().'/api/v1/auth/login');

            if (! $response->successful()) {
                throw new RuntimeException('Monnify login failed: '.$response->body());
            }

            $token = data_get($response->json(), 'responseBody.accessToken');
            if (! is_string($token) || $token === '') {
                throw new RuntimeException('Monnify login returned no access token.');
            }

            return $token;
        });
    }

    public function clearTokenCache(): void
    {
        Cache::forget('monnify_access_token_'.$this->provider()->id);
    }

    public function http(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->asJson()
            ->timeout(45);
    }

    public function get(string $path, array $query = []): array
    {
        return $this->requestWithAuthRetry(
            fn () => $this->http()->get($this->baseUrl().$path, $query)
        );
    }

    public function post(string $path, array $payload = []): array
    {
        return $this->requestWithAuthRetry(
            fn () => $this->http()->post($this->baseUrl().$path, $payload)
        );
    }

    public function verifySignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (! filled($signatureHeader)) {
            return false;
        }

        $computed = hash_hmac('sha512', $rawBody, $this->secretKey());
        // Docs also describe SHA-512(secret + body)
        $alt = hash('sha512', $this->secretKey().$rawBody);

        return hash_equals($computed, $signatureHeader) || hash_equals($alt, $signatureHeader);
    }

    /**
     * @param  callable(): Response  $make
     * @return array<string, mixed>
     */
    private function requestWithAuthRetry(callable $make): array
    {
        $response = $make();

        if ($response->status() === 401) {
            $this->clearTokenCache();
            $response = $make();
        }

        $this->throwIfFailed($response->status(), $response->json() ?? [], $response->body());

        return $response->json() ?? [];
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function throwIfFailed(int $status, array $json, string $body): void
    {
        $ok = (bool) ($json['requestSuccessful'] ?? false);
        if ($status >= 200 && $status < 300 && $ok) {
            return;
        }

        $code = (string) ($json['responseCode'] ?? $status);
        $message = (string) ($json['responseMessage'] ?? $body);

        throw new MonnifyApiException($message, $code, $json);
    }
}
