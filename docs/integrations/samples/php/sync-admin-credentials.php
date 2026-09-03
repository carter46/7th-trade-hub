<?php
/**
 * Owned tools: POST admin email and/or password changes to Hub.
 * Does not rotate keys or affect Check connection.
 *
 * Requires protocol-v1-verify.php in the same folder (canonical HMAC).
 */

declare(strict_types=1);

require_once __DIR__.'/protocol-v1-verify.php';

/**
 * @param  array{email?: string, password?: string}  $changes
 */
function seventh_tradehub_sync_admin_credentials(
    string $hubUrl,
    string $integrationId,
    string $clientId,
    string $clientSecret,
    string $webhookSecret,
    array $changes,
): void {
    $email = isset($changes['email']) ? strtolower(trim((string) $changes['email'])) : '';
    $password = isset($changes['password']) ? (string) $changes['password'] : '';
    if ($email === '' && $password === '') {
        throw new InvalidArgumentException('Pass email and/or password.');
    }

    $now = new DateTimeImmutable('now');
    $payload = [
        'integration_id' => $integrationId,
        'context' => 'owned_tool',
        'role' => 'credential_sync',
        'event' => 'owned.admin_credentials.updated',
        'event_id' => bin2hex(random_bytes(16)),
        'request_id' => bin2hex(random_bytes(16)),
        'nonce' => bin2hex(random_bytes(12)),
        'issued_at' => $now->format(DateTimeInterface::ATOM),
        'expires_at' => $now->modify('+3 minutes')->format(DateTimeInterface::ATOM),
    ];
    if ($email !== '') {
        $payload['identity'] = ['email' => $email];
    }
    if ($password !== '') {
        $payload['credential'] = ['password' => $password];
    }

    $signed = seventh_tradehub_sign($payload, $clientSecret);
    $url = rtrim($hubUrl, '/').'/webhooks/site-integrations/'.$integrationId;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-7TH-Webhook-Secret: '.$webhookSecret,
            'X-7TH-Client-Id: '.$clientId,
        ],
        CURLOPT_POSTFIELDS => json_encode($signed, JSON_UNESCAPED_SLASHES),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('Hub credential sync failed HTTP '.$status.': '.$body);
    }
}
