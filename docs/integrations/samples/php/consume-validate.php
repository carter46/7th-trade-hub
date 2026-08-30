<?php
/**
 * Minimal merchant health + consume sketch (framework-agnostic PHP).
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

// Example consume entry:
// $body = seventh_tradehub_validate_token($_GET['token'] ?? '');
// if ($body) { /* find local user by $body['identity']['email']; start session */ }
