<?php

namespace Tests\Unit\SiteIntegrations;

use App\Services\SiteIntegrations\ConnectionCheckService;
use App\Services\SiteIntegrations\IntegrationHttpClient;
use App\Services\SiteIntegrations\ProtocolV1Signer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class ConnectionCheckDiagnosticsTest extends TestCase
{
    public function test_failure_message_includes_http_status_content_type_and_body_excerpt(): void
    {
        Http::fake([
            'https://merchant.example/api/7th-tradehub/v1/health' => Http::response(
                '<html>Checking your browser before accessing</html>',
                403,
                ['Content-Type' => 'text/html; charset=UTF-8']
            ),
        ]);

        $service = $this->makeService();
        $result = $this->invokePing($service, 'https://merchant.example/api/7th-tradehub/v1/health');

        $this->assertFalse($result['ok']);
        $this->assertSame(403, $result['http_status']);
        $this->assertStringContainsString('Health check failed: HTTP 403', $result['message']);
        $this->assertStringContainsString('Content-Type: text/html', $result['message']);
        $this->assertStringContainsString('invalid JSON response', $result['message']);
        $this->assertStringContainsString('Checking your browser', $result['message']);
        $this->assertStringContainsString('URL: https://merchant.example/api/7th-tradehub/v1/health', $result['message']);
        $this->assertFalse($result['payload']['json_decodable']);
    }

    public function test_failure_message_includes_json_error_fields(): void
    {
        Http::fake([
            'https://merchant.example/api/7th-tradehub/v1/health' => Http::response(
                ['ok' => false, 'error' => 'invalid_signature', 'message' => 'HMAC verification failed.'],
                401,
                ['Content-Type' => 'application/json']
            ),
        ]);

        $service = $this->makeService();
        $result = $this->invokePing($service, 'https://merchant.example/api/7th-tradehub/v1/health');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('HTTP 401', $result['message']);
        $this->assertStringContainsString('JSON ok=false', $result['message']);
        $this->assertStringContainsString('error=invalid_signature', $result['message']);
        $this->assertStringContainsString('message=HMAC verification failed.', $result['message']);
    }

    public function test_empty_body_200_without_ok_is_described(): void
    {
        Http::fake([
            'https://merchant.example/api/7th-tradehub/v1/health' => Http::response('', 200, ['Content-Type' => 'text/plain']),
        ]);

        $service = $this->makeService();
        $result = $this->invokePing($service, 'https://merchant.example/api/7th-tradehub/v1/health');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('HTTP 200', $result['message']);
        $this->assertStringContainsString('invalid/empty JSON response', $result['message']);
        $this->assertStringContainsString('response: (empty body)', $result['message']);
    }

    public function test_redirect_is_flagged_when_not_followed(): void
    {
        Http::fake([
            'https://merchant.example/api/7th-tradehub/v1/health' => Http::response(
                '',
                302,
                ['Location' => 'https://merchant.example/cdn-cgi/challenge']
            ),
        ]);

        $service = $this->makeService();
        $result = $this->invokePing($service, 'https://merchant.example/api/7th-tradehub/v1/health');

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Redirected: yes → https://merchant.example/cdn-cgi/challenge', $result['message']);
    }

    public function test_network_error_is_described(): void
    {
        Http::fake(function (Request $request) {
            throw new ConnectionException('cURL error 28: Connection timed out');
        });

        $service = $this->makeService();
        $result = $this->invokePing($service, 'https://merchant.example/api/7th-tradehub/v1/health');

        $this->assertFalse($result['ok']);
        $this->assertNull($result['http_status']);
        $this->assertStringContainsString('Network/cURL error:', $result['message']);
        $this->assertStringContainsString('Connection timed out', $result['message']);
    }

    public function test_success_still_passes_with_ok_true(): void
    {
        Http::fake([
            'https://merchant.example/api/7th-tradehub/v1/health' => Http::response(
                ['ok' => true, 'capabilities' => ['health', 'demo_user_login', 'demo_admin_login']],
                200,
                ['Content-Type' => 'application/json']
            ),
        ]);

        $service = $this->makeService();
        $result = $this->invokePing($service, 'https://merchant.example/api/7th-tradehub/v1/health');

        $this->assertTrue($result['ok']);
        $this->assertStringContainsString('Connection successful.', $result['message']);
        $this->assertSame(true, $result['payload']['ok']);
    }

    private function makeService(): ConnectionCheckService
    {
        return new ConnectionCheckService(
            app(ProtocolV1Signer::class),
            app(IntegrationHttpClient::class),
            Mockery::mock(\App\Services\SiteIntegrations\SubscriptionSyncService::class),
            Mockery::mock(\App\Services\Notifications\NotificationDispatcher::class),
        );
    }

    /**
     * @return array{ok: bool, http_status: int|null, message: string, payload: array<string, mixed>|null}
     */
    private function invokePing(ConnectionCheckService $service, string $url): array
    {
        $method = new \ReflectionMethod(ConnectionCheckService::class, 'ping');
        $method->setAccessible(true);

        return $method->invoke(
            $service,
            $url,
            '550e8400-e29b-41d4-a716-446655440000',
            'client-id-example',
            'client-secret-example-not-logged',
            'demo',
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
