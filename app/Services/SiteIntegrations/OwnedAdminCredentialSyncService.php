<?php

namespace App\Services\SiteIntegrations;

use App\Models\SiteIntegrationCheckLog;
use App\Models\UserTool;
use App\Models\UserToolIntegration;
use App\Modules\Admin\Services\AuditLogService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class OwnedAdminCredentialSyncService
{
    public const EVENT = 'owned.admin_credentials.updated';

    public function __construct(
        private ProtocolV1Signer $signer,
        private AuditLogService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: true, deduped?: true}
     */
    public function apply(UserToolIntegration $integration, array $payload, ?string $ip = null): array
    {
        $this->assertValidAssertion($integration, $payload);

        $email = $this->optionalEmail($payload);
        $password = $this->optionalPassword($payload);

        if ($email === null && $password === null) {
            throw new InvalidArgumentException('Provide identity.email and/or credential.password.');
        }

        $tool = $integration->userTool;
        if (! $tool instanceof UserTool) {
            throw new InvalidArgumentException('Owned tool not found for this integration.');
        }

        $eventId = (string) $payload['event_id'];
        $cacheKey = self::EVENT.':'.$integration->integration_id.':'.$eventId;

        if (! Cache::add($cacheKey, 1, now()->addDay())) {
            return ['ok' => true, 'deduped' => true];
        }

        $previousEmail = $tool->admin_email;
        $emailUpdated = false;
        $passwordUpdated = false;

        if ($email !== null) {
            $tool->admin_email = $email;
            $emailUpdated = $previousEmail !== $email;
        }

        if ($password !== null) {
            $passwordUpdated = $tool->admin_password !== $password;
            $tool->admin_password = $password;
        }

        try {
            $tool->save();
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);

            throw $e;
        }

        $this->audit->log(null, 'user_tool.admin_credentials_synced', $tool, [
            'admin_email' => $previousEmail,
            'password_updated' => false,
        ], [
            'admin_email' => $tool->admin_email,
            'password_updated' => $passwordUpdated,
            'email_updated' => $emailUpdated,
            'event_id' => $eventId,
        ], $ip, [
            'actor_type' => 'integration',
            'actor_id' => null,
            'module' => 'site_integrations',
        ]);

        SiteIntegrationCheckLog::create([
            'owner_type' => 'owned',
            'owner_id' => $integration->id,
            'direction' => 'site_to_hub',
            'ok' => true,
            'http_status' => 200,
            'message' => 'Admin credentials updated',
            'payload_summary' => [
                'event' => self::EVENT,
                'event_id' => $eventId,
                'email_updated' => $emailUpdated,
                'password_updated' => $passwordUpdated,
            ],
        ]);

        return ['ok' => true];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertValidAssertion(UserToolIntegration $integration, array $payload): void
    {
        if (($payload['event'] ?? null) !== self::EVENT) {
            throw new InvalidArgumentException('Unknown credential event.');
        }

        if (($payload['context'] ?? null) !== 'owned_tool') {
            throw new InvalidArgumentException('Credential sync requires context owned_tool.');
        }

        if (($payload['role'] ?? null) !== 'credential_sync') {
            throw new InvalidArgumentException('Credential sync requires role credential_sync.');
        }

        $bodyIntegrationId = (string) ($payload['integration_id'] ?? '');
        if ($bodyIntegrationId === '' || ! hash_equals($integration->integration_id, $bodyIntegrationId)) {
            throw new InvalidArgumentException('integration_id does not match this webhook.');
        }

        $eventId = $payload['event_id'] ?? null;
        if (! is_string($eventId) || trim($eventId) === '' || strlen($eventId) > 64) {
            throw new InvalidArgumentException('event_id is required (string, max 64 characters).');
        }

        foreach (['request_id', 'nonce', 'issued_at'] as $field) {
            $value = $payload[$field] ?? null;
            if (! is_string($value) || trim($value) === '') {
                throw new InvalidArgumentException($field.' is required.');
            }
        }

        if (! $this->signer->verify($payload, $integration->client_secret)) {
            throw new InvalidArgumentException('Invalid Protocol v1 signature.');
        }

        $expiresAt = $payload['expires_at'] ?? null;
        if (! is_string($expiresAt) || $expiresAt === '') {
            throw new InvalidArgumentException('expires_at is required.');
        }

        try {
            $expiry = Carbon::parse($expiresAt);
        } catch (\Throwable) {
            throw new InvalidArgumentException('expires_at is invalid.');
        }

        if ($expiry->lessThan(now())) {
            throw new InvalidArgumentException('Assertion has expired.');
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function optionalEmail(array $payload): ?string
    {
        $identity = $payload['identity'] ?? null;
        if (! is_array($identity) || ! array_key_exists('email', $identity) || $identity['email'] === null || $identity['email'] === '') {
            return null;
        }

        $email = strtolower(trim((string) $identity['email']));
        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']]);
        if ($validator->fails()) {
            throw new InvalidArgumentException('identity.email is invalid.');
        }

        return $email;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function optionalPassword(array $payload): ?string
    {
        $credential = $payload['credential'] ?? null;
        if (! is_array($credential) || ! array_key_exists('password', $credential) || $credential['password'] === null || $credential['password'] === '') {
            return null;
        }

        $password = (string) $credential['password'];
        if (strlen($password) < 6 || strlen($password) > 255) {
            throw new InvalidArgumentException('credential.password must be between 6 and 255 characters.');
        }

        return $password;
    }
}
