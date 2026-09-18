<?php
/**
 * Fallback Hub GET for owned-tool subscription (defense in depth).
 *
 * Hub push (POST …/subscription/sync) is primary — do not run this on a tight cron
 * for every site. Call only when local state is missing/stale, and throttle with
 * last_reconciliation_attempt_at (see NON-AUTHENTICATED-SITE.md).
 *
 * Offline when status is expired|suspended|cancelled|inactive, or expires_at is past:
 * - authenticated sites: public session-expired; admin post-login Hub CTAs
 * - non-authenticated: public shutdown overlay with Hub CTAs
 * - fail closed if last_synced_at older than max trust age and Hub unreachable
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

/**
 * @param  array<string, mixed>  $snap
 */
function seventh_tradehub_subscription_is_offline(array $snap): bool
{
    $status = (string) ($snap['status'] ?? '');
    if (in_array($status, ['expired', 'suspended', 'cancelled', 'inactive'], true)) {
        return true;
    }

    $expiresAt = $snap['expires_at'] ?? null;
    if (is_string($expiresAt) && $expiresAt !== '' && strtotime($expiresAt) < time()) {
        return true;
    }

    return false;
}

/**
 * Suggested copy + Hub CTA for a regular admin AFTER successful password login
 * while the subscription is offline. Do not use this on public pages —
 * public pages keep the generic session-expired UI.
 *
 * @return array{message: string, cta_label: string, cta_url: string}|null
 */
function seventh_tradehub_admin_shutdown_message(array $snap, string $hubBaseUrl): ?array
{
    if (! seventh_tradehub_subscription_is_offline($snap)) {
        return null;
    }

    $hub = rtrim($hubBaseUrl, '/');
    $status = (string) ($snap['status'] ?? 'expired');

    return match ($status) {
        'cancelled' => [
            'message' => 'This website subscription has been cancelled. Contact 7th Trade Hub support for help.',
            'cta_label' => 'Open Help Center',
            'cta_url' => $hub.'/help',
        ],
        'suspended' => [
            'message' => 'This website has been suspended. Contact 7th Trade Hub support for help.',
            'cta_label' => 'Open Help Center',
            'cta_url' => $hub.'/help',
        ],
        'inactive' => [
            'message' => 'This website is inactive. Contact 7th Trade Hub support for help.',
            'cta_label' => 'Open Help Center',
            'cta_url' => $hub.'/help',
        ],
        default => [ // expired, or past expires_at with stale status
            'message' => 'Your website subscription has expired. Sign in to your 7th Trade Hub account to renew this website subscription.',
            'cta_label' => 'Sign in to 7th Trade Hub',
            'cta_url' => $hub.'/login',
        ],
    };
}

// Example:
// $snap = seventh_tradehub_poll_subscription();
// if ($snap && seventh_tradehub_subscription_is_offline($snap)) {
//     /* public pages: generic session-expired UI */
//     /* after regular-admin password login:
//        $ui = seventh_tradehub_admin_shutdown_message($snap, seventh_tradehub_env('SEVENTH_TRADEHUB_HUB_URL'));
//        show $ui['message'] + link $ui['cta_url']
//     */
//     /* only super admin may enter the admin panel */
// }
