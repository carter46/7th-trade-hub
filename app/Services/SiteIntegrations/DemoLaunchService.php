<?php

namespace App\Services\SiteIntegrations;

use App\Enums\SiteLaunchContext;
use App\Enums\SiteIntegrationStatus;
use App\Models\SiteIntegration;
use App\Models\SiteLaunchToken;
use App\Models\User;
use App\Models\UserTool;
use App\Models\UserToolIntegration;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class DemoLaunchService
{
    public function __construct(
        private ProtocolV1Signer $signer,
        private AuditLogService $audit,
    ) {}

    /**
     * @return array{redirect_url: string, assertion: array<string, mixed>}
     */
    public function launchDemo(User $hubUser, SiteIntegration $integration, string $role, ?string $ip = null, ?string $userAgent = null): array
    {
        if (! $integration->isActive()) {
            throw new InvalidArgumentException('Demo integration is not active.');
        }

        $role = strtolower($role);
        if (! in_array($role, ['user', 'admin'], true)) {
            throw new InvalidArgumentException('Invalid demo role.');
        }

        $capability = $role === 'admin'
            ? SiteIntegration::CAP_DEMO_ADMIN_LOGIN
            : SiteIntegration::CAP_DEMO_USER_LOGIN;

        if (! $integration->hasCapability($capability)) {
            throw new InvalidArgumentException('This demo does not allow login as '.$role.'.');
        }

        $email = $role === 'admin'
            ? $integration->demo_admin_email
            : $integration->demo_user_email;

        if (! is_string($email) || $email === '') {
            throw new InvalidArgumentException('Demo '.$role.' email is not configured.');
        }

        return $this->issueLaunch(
            context: SiteLaunchContext::Demo,
            role: $role,
            boundEmail: $email,
            integrationId: $integration->integration_id,
            clientSecret: $integration->client_secret,
            consumeBaseUrl: $integration->consumeUrl(),
            hubUser: $hubUser,
            siteIntegrationId: $integration->id,
            userToolId: null,
            ip: $ip,
            userAgent: $userAgent,
        );
    }

    /**
     * @return array{redirect_url: string, assertion: array<string, mixed>}
     */
    public function launchOwnedAdmin(User $hubUser, UserTool $tool, ?string $ip = null, ?string $userAgent = null): array
    {
        if ($tool->user_id !== $hubUser->id) {
            throw new InvalidArgumentException('Tool does not belong to this user.');
        }

        if (! $tool->isSubscriptionLive()) {
            throw new InvalidArgumentException('This tool is not active or its subscription has expired.');
        }

        if (! $tool->admin_email) {
            throw new InvalidArgumentException('Admin email is not configured for this tool.');
        }

        $integration = $tool->integration;
        if (! $integration instanceof UserToolIntegration) {
            throw new InvalidArgumentException('Provisioning integration is missing.');
        }

        if (! $integration->hasCapability(UserToolIntegration::CAP_OWNED_ADMIN_LOGIN)) {
            throw new InvalidArgumentException('Owned admin login is not enabled for this tool.');
        }

        return $this->issueLaunch(
            context: SiteLaunchContext::OwnedTool,
            role: 'admin',
            boundEmail: $tool->admin_email,
            integrationId: $integration->integration_id,
            clientSecret: $integration->client_secret,
            consumeBaseUrl: $tool->consumeUrl(),
            hubUser: $hubUser,
            siteIntegrationId: null,
            userToolId: $tool->id,
            ip: $ip,
            userAgent: $userAgent,
        );
    }

    /**
     * Validate and consume a one-time token (for sites that call Hub back).
     *
     * @return array{valid: true, protocol: string, version: int, context: string, role: string, identity: array{email: string}, integration_id: string, expires_at: string}
     */
    public function validateAndConsume(string $plainToken, string $clientId): array
    {
        $hash = hash('sha256', $plainToken);

        return DB::transaction(function () use ($hash, $clientId, $plainToken) {
            $token = SiteLaunchToken::query()
                ->where('token_hash', $hash)
                ->lockForUpdate()
                ->first();

            if (! $token || ! $token->isUsable()) {
                throw new InvalidArgumentException('Invalid, expired, or already used token.');
            }

            if ($token->user_tool_id) {
                $tool = UserTool::query()->whereKey($token->user_tool_id)->lockForUpdate()->first();
                if (! $tool || ! $tool->isSubscriptionLive()) {
                    throw new InvalidArgumentException('Invalid, expired, or already used token.');
                }
            }

            $secret = $this->resolveSecretForToken($token, $clientId);

            $assertion = [
                'protocol' => ProtocolV1Signer::PROTOCOL,
                'version' => ProtocolV1Signer::VERSION,
                'integration_id' => $token->integration_id,
                'context' => $token->context->value,
                'role' => $token->role,
                'identity' => ['email' => $token->bound_email],
                'request_id' => $token->request_id,
                'nonce' => $token->nonce,
                'issued_at' => $token->created_at?->toIso8601String(),
                'expires_at' => $token->expires_at->toIso8601String(),
                'token' => $plainToken,
            ];

            // Sites may also verify locally with HMAC; Hub validate confirms single-use.
            unset($assertion['token']);

            $token->consumed_at = now();
            $token->save();

            return [
                'valid' => true,
                'protocol' => ProtocolV1Signer::PROTOCOL,
                'version' => ProtocolV1Signer::VERSION,
                'context' => $token->context->value,
                'role' => $token->role,
                'identity' => ['email' => $token->bound_email],
                'integration_id' => $token->integration_id,
                'expires_at' => $token->expires_at->toIso8601String(),
                'client_secret_hint' => substr(hash('sha256', $secret), 0, 8),
            ];
        });
    }

    /**
     * @return array{redirect_url: string, assertion: array<string, mixed>}
     */
    private function issueLaunch(
        SiteLaunchContext $context,
        string $role,
        string $boundEmail,
        string $integrationId,
        string $clientSecret,
        string $consumeBaseUrl,
        User $hubUser,
        ?int $siteIntegrationId,
        ?int $userToolId,
        ?string $ip,
        ?string $userAgent,
    ): array {
        $plainToken = Str::random(64);
        $nonce = Str::random(32);
        $requestId = (string) Str::uuid();
        $expiresAt = now()->addSeconds(120);

        $assertion = $this->signer->sign([
            'integration_id' => $integrationId,
            'context' => $context->value,
            'role' => $role,
            'identity' => ['email' => $boundEmail],
            'request_id' => $requestId,
            'nonce' => $nonce,
            'issued_at' => now()->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'token' => $plainToken,
        ], $clientSecret);

        SiteLaunchToken::create([
            'token_hash' => hash('sha256', $plainToken),
            'context' => $context,
            'role' => $role,
            'integration_id' => $integrationId,
            'bound_email' => $boundEmail,
            'hub_user_id' => $hubUser->id,
            'site_integration_id' => $siteIntegrationId,
            'user_tool_id' => $userToolId,
            'request_id' => $requestId,
            'nonce' => $nonce,
            'expires_at' => $expiresAt,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        $this->audit->log($hubUser->id, 'site_integration.launch', null, null, [
            'context' => $context->value,
            'role' => $role,
            'integration_id' => $integrationId,
            'user_tool_id' => $userToolId,
            'site_integration_id' => $siteIntegrationId,
        ], $ip, [
            'actor_type' => 'user',
            'actor_id' => $hubUser->id,
            'module' => 'site_integrations',
        ]);

        $redirectUrl = $consumeBaseUrl.'?'.http_build_query([
            'token' => $plainToken,
            'integration_id' => $integrationId,
        ]);

        return [
            'redirect_url' => $redirectUrl,
            'assertion' => $assertion,
        ];
    }

    private function resolveSecretForToken(SiteLaunchToken $token, string $clientId): string
    {
        if ($token->context === SiteLaunchContext::Demo) {
            $integration = SiteIntegration::query()
                ->where('integration_id', $token->integration_id)
                ->where('client_id', $clientId)
                ->where('status', SiteIntegrationStatus::Active)
                ->first();

            if (! $integration) {
                throw new RuntimeException('Unknown demo integration credentials.');
            }

            return $integration->client_secret;
        }

        $integration = UserToolIntegration::query()
            ->where('integration_id', $token->integration_id)
            ->where('client_id', $clientId)
            ->first();

        if (! $integration) {
            throw new RuntimeException('Unknown owned integration credentials.');
        }

        return $integration->client_secret;
    }
}
