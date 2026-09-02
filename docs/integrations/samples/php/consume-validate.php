<?php
/**
 * Minimal merchant consume + Hub validate sketch (framework-agnostic PHP).
 * Copy patterns into your app; do not treat as production-complete.
 */

declare(strict_types=1);

function seventh_tradehub_env(string $key): string
{
    return (string) (getenv($key) ?: '');
}

/**
 * Call Hub to validate a one-time launch token.
 *
 * @return array<string, mixed>|null
 */
function seventh_tradehub_validate_token(string $token): ?array
{
    $hub = rtrim(seventh_tradehub_env('SEVENTH_TRADEHUB_HUB_URL'), '/');
    $ch = curl_init($hub.'/api/site-integrations/v1/demo/tokens/validate');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-7TH-Client-Id: '.seventh_tradehub_env('SEVENTH_TRADEHUB_CLIENT_ID'),
            'X-7TH-Client-Secret: '.seventh_tradehub_env('SEVENTH_TRADEHUB_CLIENT_SECRET'),
        ],
        CURLOPT_POSTFIELDS => json_encode(['token' => $token], JSON_THROW_ON_ERROR),
        CURLOPT_TIMEOUT => 15,
    ]);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || ! is_string($raw)) {
        return null;
    }
    $body = json_decode($raw, true);

    return is_array($body) && ($body['valid'] ?? false) === true ? $body : null;
}

/**
 * @param array<string, mixed> $validated
 */
function seventh_tradehub_consume_matches_env(array $validated, ?string $queryIntegrationId = null): bool
{
    $expected = seventh_tradehub_env('SEVENTH_TRADEHUB_INTEGRATION_ID');
    $fromResponse = (string) ($validated['integration_id'] ?? '');

    if ($expected === '' || $fromResponse === '' || ! hash_equals($expected, $fromResponse)) {
        return false;
    }

    if ($queryIntegrationId !== null && $queryIntegrationId !== '' && ! hash_equals($expected, $queryIntegrationId)) {
        return false;
    }

    return true;
}

// Example consume entry (GET /auth/7th-tradehub/demo/consume):
//
// $token = $_GET['token'] ?? '';
// $queryIntegrationId = $_GET['integration_id'] ?? null;
// $validated = seventh_tradehub_validate_token($token);
// if (! $validated || ! seventh_tradehub_consume_matches_env($validated, is_string($queryIntegrationId) ? $queryIntegrationId : null)) {
//     http_response_code(403);
//     exit('Invalid launch token.');
// }
//
// $email = (string) ($validated['identity']['email'] ?? '');
// $role = (string) ($validated['role'] ?? 'user');
// // Load existing local user by $email — Hub does not create users on your site.
// // Optionally verify local role matches $role.
// // establishServerSideSession($user);  // skip password / MFA / onboarding
// // header('Location: ' . ($role === 'admin' ? '/admin' : '/dashboard'));
