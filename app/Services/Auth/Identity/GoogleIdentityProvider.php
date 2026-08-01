<?php

namespace App\Services\Auth\Identity;

use App\Models\IntegrationProvider;
use App\Models\UserAuthProvider;
use InvalidArgumentException;

class GoogleIdentityProvider implements ExternalIdentityProviderInterface
{
    public function __construct(
        private readonly GoogleIdTokenVerifier $verifier,
    ) {}

    public function name(): string
    {
        return UserAuthProvider::GOOGLE;
    }

    public function isAvailable(): bool
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_IDENTITY);

        return $row->enabled && filled($row->credential('client_id'));
    }

    public function verifyCredential(string $credential): VerifiedIdentity
    {
        if (! $this->isAvailable()) {
            throw new InvalidArgumentException('Google Sign-In is not enabled.');
        }

        $row = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_IDENTITY);
        $clientId = (string) $row->credential('client_id', '');
        $payload = $this->verifier->verify($credential, $clientId);

        $emailVerified = ($payload['email_verified'] ?? false) === true
            || ($payload['email_verified'] ?? '') === 'true';

        return new VerifiedIdentity(
            provider: UserAuthProvider::GOOGLE,
            providerUserId: (string) $payload['sub'],
            email: strtolower(trim((string) $payload['email'])),
            emailVerified: $emailVerified,
            name: filled($payload['name'] ?? null) ? (string) $payload['name'] : null,
            avatarUrl: filled($payload['picture'] ?? null) ? (string) $payload['picture'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function configForFrontend(): array
    {
        $row = IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_IDENTITY);
        $meta = $row->meta ?? [];
        $clientId = (string) $row->credential('client_id', '');
        $enabled = $row->enabled && $clientId !== '';

        return [
            'enabled' => $enabled,
            'client_id' => $enabled ? $clientId : '',
            'one_tap_enabled' => $enabled && (bool) ($meta['one_tap_enabled'] ?? false),
            'auto_select_enabled' => (bool) ($meta['auto_select_enabled'] ?? false),
            'one_tap_show_home' => (bool) ($meta['one_tap_show_home'] ?? true),
            'one_tap_show_login' => (bool) ($meta['one_tap_show_login'] ?? false),
            'one_tap_show_register' => (bool) ($meta['one_tap_show_register'] ?? false),
            'one_tap_disable_after_dismiss' => (bool) ($meta['one_tap_disable_after_dismiss'] ?? true),
            'one_tap_prompt_cooldown_hours' => max(1, (int) ($meta['one_tap_prompt_cooldown_hours'] ?? 24)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultMeta(): array
    {
        return [
            'one_tap_enabled' => false,
            'auto_select_enabled' => false,
            'one_tap_show_home' => true,
            'one_tap_show_login' => false,
            'one_tap_show_register' => false,
            'one_tap_disable_after_dismiss' => true,
            'one_tap_prompt_cooldown_hours' => 24,
        ];
    }
}
