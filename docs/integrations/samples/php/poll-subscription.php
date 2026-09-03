<?php
/**
 * Poll Hub for owned-tool subscription (defense in depth).
 * Schedule every 5–15 minutes. When expired (or Admin Shutdown Site):
 * fail-closed for users/regular admins; keep login page/form up;
 * only super admin may enter after password login; refuse Hub SSO.
 */

declare(strict_types=1);

require __DIR__.'/consume-validate.php';

/**
 * @return array<string, mixed>|null
 */
function seventh_tradehub_poll_subscription(): ?array
{
    $hub = rtrim(seventh_tradehub_env('SEVENTH_TRADEHUB_HUB_URL'), '/');
    $integrationId = seventh_tradehub_env('SEVENTH_TRADEHUB_INTEGRATION_ID');

    $ch = curl_init($hub.'/api/site-integrations/v1/subscription');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'X-7TH-Client-Id: '.seventh_tradehub_env('SEVENTH_TRADEHUB_CLIENT_ID'),
            'X-7TH-Client-Secret: '.seventh_tradehub_env('SEVENTH_TRADEHUB_CLIENT_SECRET'),
            'X-7TH-Integration-Id: '.$integrationId,
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || ! is_string($raw)) {
        return null;
    }

    $body = json_decode($raw, true);

    return is_array($body) ? $body : null;
}

// Example:
// $snap = seventh_tradehub_poll_subscription();
// if ($snap && (($snap['status'] ?? '') === 'expired' || strtotime((string) ($snap['expires_at'] ?? '')) < time())) {
//     /* fail-closed UI; except login; only super admin after password auth */
// }
