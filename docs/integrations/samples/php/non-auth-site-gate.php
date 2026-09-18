<?php
/**
 * Non-authenticated owned site gate (Protocol v1).
 *
 * Hub push is primary: POST …/subscription/sync updates local state.
 * Page-load Hub poll is a throttled fallback only (not every visitor).
 *
 * Wire:
 * - Include this file (or call seventh_tradehub_gate()) at site entry.
 * - Route POST /api/7th-tradehub/v1/health and …/subscription/sync here
 *   (or copy the handlers into your router).
 *
 * Requires protocol-v1-verify.php in the same folder.
 */

declare(strict_types=1);

require_once __DIR__.'/protocol-v1-verify.php';

// ---------------------------------------------------------------------------
// CONFIG — fill from Hub owned-tool credentials
// ---------------------------------------------------------------------------
const SEVENTH_TRADEHUB_HUB_URL = 'https://7th-tradehub.online';
const SEVENTH_TRADEHUB_INTEGRATION_ID = 'REPLACE_INTEGRATION_ID';
const SEVENTH_TRADEHUB_CLIENT_ID = 'REPLACE_CLIENT_ID';
const SEVENTH_TRADEHUB_CLIENT_SECRET = 'REPLACE_CLIENT_SECRET';
const SEVENTH_TRADEHUB_WEBHOOK_SECRET = 'REPLACE_WEBHOOK_SECRET'; // optional for this gate

/** Seconds: local "active" without a successful Hub sync is not trusted after this. */
const SEVENTH_TRADEHUB_MAX_TRUST_AGE = 86400; // 24 hours

/** Seconds: minimum gap between Hub GET reconciliation attempts. */
const SEVENTH_TRADEHUB_FALLBACK_RECONCILE_INTERVAL = 900; // 15 minutes

/** Writable path for local subscription state (prefer outside public web root). */
const SEVENTH_TRADEHUB_STATE_FILE = __DIR__.'/../storage/7th-tradehub-subscription.json';

// ---------------------------------------------------------------------------
// HTTP handlers (health + sync) — call from your front controller when path matches
// ---------------------------------------------------------------------------

/**
 * Dispatch Hub→site endpoints. Returns true if a request was handled.
 */
function seventh_tradehub_handle_hub_request(): bool
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    if ($method === 'POST' && str_ends_with($uri, '/api/7th-tradehub/v1/health')) {
        seventh_tradehub_handle_health();

        return true;
    }

    if ($method === 'POST' && str_ends_with($uri, '/api/7th-tradehub/v1/subscription/sync')) {
        seventh_tradehub_handle_subscription_sync();

        return true;
    }

    return false;
}

function seventh_tradehub_handle_health(): void
{
    $raw = file_get_contents('php://input') ?: '';
    $payload = json_decode($raw, true);
    if (! is_array($payload)) {
        seventh_tradehub_json_response(['ok' => false, 'error' => 'invalid_json'], 400);
    }

    try {
        seventh_tradehub_verify($payload, SEVENTH_TRADEHUB_CLIENT_SECRET);
    } catch (Throwable $e) {
        seventh_tradehub_json_response(['ok' => false, 'error' => 'invalid_signature'], 401);
    }

    if (($payload['integration_id'] ?? null) !== SEVENTH_TRADEHUB_INTEGRATION_ID) {
        seventh_tradehub_json_response(['ok' => false, 'error' => 'integration_mismatch'], 401);
    }

    seventh_tradehub_json_response([
        'ok' => true,
        'capabilities' => ['health', 'subscription_sync', 'shutdown_on_expiry'],
    ]);
}

function seventh_tradehub_handle_subscription_sync(): void
{
    $raw = file_get_contents('php://input') ?: '';
    $payload = json_decode($raw, true);
    if (! is_array($payload)) {
        seventh_tradehub_json_response(['ok' => false, 'error' => 'invalid_json'], 400);
    }

    try {
        seventh_tradehub_verify($payload, SEVENTH_TRADEHUB_CLIENT_SECRET);
    } catch (Throwable $e) {
        seventh_tradehub_json_response(['ok' => false, 'error' => 'invalid_signature'], 401);
    }

    if (($payload['integration_id'] ?? null) !== SEVENTH_TRADEHUB_INTEGRATION_ID) {
        seventh_tradehub_json_response(['ok' => false, 'error' => 'integration_mismatch'], 401);
    }

    $sub = $payload['subscription'] ?? null;
    if (! is_array($sub)) {
        seventh_tradehub_json_response(['ok' => false, 'error' => 'missing_subscription'], 422);
    }

    $current = seventh_tradehub_load_state();
    if (! seventh_tradehub_should_apply_subscription($sub, $current)) {
        // Stale/out-of-order push: ACK so Hub does not retry, but do not reopen the site.
        seventh_tradehub_json_response(['ok' => true, 'applied' => false]);
    }

    $state = is_array($current) ? $current : [];
    $state['status'] = (string) ($sub['status'] ?? 'expired');
    $state['expires_at'] = isset($sub['expires_at']) ? (string) $sub['expires_at'] : null;
    $state['updated_at'] = isset($sub['updated_at']) ? (string) $sub['updated_at'] : null;
    $state['last_synced_at'] = gmdate('c');
    seventh_tradehub_save_state($state);

    seventh_tradehub_json_response(['ok' => true, 'applied' => true]);
}

// ---------------------------------------------------------------------------
// Gate (call at the start of every public page)
// ---------------------------------------------------------------------------

/**
 * Enforce subscription. Exits with shutdown HTML when offline.
 */
function seventh_tradehub_gate(): void
{
    $state = seventh_tradehub_reconcile_if_needed(seventh_tradehub_load_state());

    if (seventh_tradehub_is_offline($state)) {
        seventh_tradehub_render_shutdown($state);
        exit;
    }
}

/**
 * @param  array<string, mixed>|null  $state
 * @return array<string, mixed>|null
 */
function seventh_tradehub_reconcile_if_needed(?array $state): ?array
{
    if (! seventh_tradehub_needs_reconciliation($state)) {
        return $state;
    }

    $lockPath = SEVENTH_TRADEHUB_STATE_FILE.'.lock';
    $lockDir = dirname($lockPath);
    if (! is_dir($lockDir)) {
        mkdir($lockDir, 0750, true);
    }

    $fp = @fopen($lockPath, 'c+');
    if ($fp === false) {
        // Best-effort without lock (some hosts disallow flock / lock files).
        return seventh_tradehub_reconcile_unlocked($state);
    }

    if (! flock($fp, LOCK_EX | LOCK_NB)) {
        // Another visitor is already reconciling — avoid Hub stampede.
        fclose($fp);

        return seventh_tradehub_load_state() ?? $state;
    }

    try {
        // Re-read under lock: peer may have finished reconcile.
        $state = seventh_tradehub_load_state();
        if (! seventh_tradehub_needs_reconciliation($state)) {
            return $state;
        }
        if (! seventh_tradehub_throttle_allows($state)) {
            return $state;
        }

        return seventh_tradehub_run_reconciliation($state);
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/**
 * @param  array<string, mixed>|null  $state
 * @return array<string, mixed>|null
 */
function seventh_tradehub_reconcile_unlocked(?array $state): ?array
{
    if (! seventh_tradehub_throttle_allows($state)) {
        return $state;
    }

    return seventh_tradehub_run_reconciliation($state);
}

/**
 * @param  array<string, mixed>|null  $state
 * @return array<string, mixed>|null
 */
function seventh_tradehub_run_reconciliation(?array $state): ?array
{
    $state = $state ?? [];
    $state['last_reconciliation_attempt_at'] = gmdate('c');
    seventh_tradehub_save_state($state);

    $snap = seventh_tradehub_fetch_subscription_snapshot();
    if ($snap === null) {
        return seventh_tradehub_load_state();
    }

    $current = seventh_tradehub_load_state();
    if (! seventh_tradehub_should_apply_subscription($snap, $current)) {
        return $current;
    }

    $next = is_array($current) ? $current : [];
    $next['status'] = (string) ($snap['status'] ?? 'expired');
    $next['expires_at'] = isset($snap['expires_at']) ? (string) $snap['expires_at'] : null;
    $next['updated_at'] = isset($snap['updated_at']) ? (string) $snap['updated_at'] : null;
    $next['last_synced_at'] = gmdate('c');
    // Preserve throttle marker from this attempt.
    if (isset($state['last_reconciliation_attempt_at'])) {
        $next['last_reconciliation_attempt_at'] = $state['last_reconciliation_attempt_at'];
    }
    seventh_tradehub_save_state($next);

    return $next;
}

/**
 * Monotonic apply: never let an older ACTIVE overwrite a newer offline status.
 *
 * @param  array<string, mixed>  $incoming  subscription object or Hub GET snapshot
 * @param  array<string, mixed>|null  $current
 */
function seventh_tradehub_should_apply_subscription(array $incoming, ?array $current): bool
{
    if ($current === null || ! isset($current['status'])) {
        return true;
    }

    $inUpdated = seventh_tradehub_parse_time($incoming['updated_at'] ?? null);
    $curUpdated = seventh_tradehub_parse_time($current['updated_at'] ?? null);

    if ($inUpdated !== null && $curUpdated !== null && $inUpdated < $curUpdated) {
        return false;
    }

    if ($inUpdated !== null && $curUpdated !== null && $inUpdated > $curUpdated) {
        return true;
    }

    // Equal or missing updated_at: do not resurrect offline from a stale/ambiguous ACTIVE.
    $inStatus = strtolower((string) ($incoming['status'] ?? ''));
    $curStatus = strtolower((string) ($current['status'] ?? ''));
    if ($inStatus === 'active' && $curStatus !== 'active') {
        $inExpires = seventh_tradehub_parse_time($incoming['expires_at'] ?? null);
        $curExpires = seventh_tradehub_parse_time($current['expires_at'] ?? null);
        if ($inExpires === null || $curExpires === null || $inExpires <= $curExpires) {
            return false;
        }
    }

    return true;
}

function seventh_tradehub_parse_time(mixed $value): ?int
{
    if (! is_string($value) || $value === '') {
        return null;
    }

    $ts = strtotime($value);

    return $ts === false ? null : $ts;
}

/**
 * @param  array<string, mixed>|null  $state
 */
function seventh_tradehub_needs_reconciliation(?array $state): bool
{
    if ($state === null || ! isset($state['status'], $state['last_synced_at'])) {
        return true;
    }

    $status = strtolower((string) $state['status']);
    $expiresAt = $state['expires_at'] ?? null;
    if ($status === 'active' && is_string($expiresAt) && $expiresAt !== '' && strtotime($expiresAt) < time()) {
        return true;
    }

    $synced = strtotime((string) $state['last_synced_at']);
    if ($synced === false || (time() - $synced) > SEVENTH_TRADEHUB_MAX_TRUST_AGE) {
        return true;
    }

    return false;
}

/**
 * @param  array<string, mixed>|null  $state
 */
function seventh_tradehub_throttle_allows(?array $state): bool
{
    $last = $state['last_reconciliation_attempt_at'] ?? null;
    if (! is_string($last) || $last === '') {
        return true;
    }

    $ts = strtotime($last);

    return $ts === false || (time() - $ts) >= SEVENTH_TRADEHUB_FALLBACK_RECONCILE_INTERVAL;
}

/**
 * Enforcement: only explicit active (and not past expiry / stale trust) is online.
 * pending_setup, unknown, and all other statuses are offline.
 *
 * @param  array<string, mixed>|null  $state
 */
function seventh_tradehub_is_offline(?array $state): bool
{
    if ($state === null || ! isset($state['status'])) {
        return true; // fail closed — never synced
    }

    $status = strtolower((string) $state['status']);
    if ($status !== 'active') {
        return true;
    }

    $expiresAt = $state['expires_at'] ?? null;
    if (is_string($expiresAt) && $expiresAt !== '' && strtotime($expiresAt) < time()) {
        return true; // fail closed on clock
    }

    $synced = isset($state['last_synced_at']) ? strtotime((string) $state['last_synced_at']) : false;
    if ($synced === false || (time() - $synced) > SEVENTH_TRADEHUB_MAX_TRUST_AGE) {
        // Stale active + no successful reconcile → fail closed
        return true;
    }

    return false;
}

/**
 * @return array<string, mixed>|null
 */
function seventh_tradehub_fetch_subscription_snapshot(): ?array
{
    $hub = rtrim(SEVENTH_TRADEHUB_HUB_URL, '/');
    $ch = curl_init($hub.'/api/site-integrations/v1/subscription');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'X-7TH-Client-Id: '.SEVENTH_TRADEHUB_CLIENT_ID,
            'X-7TH-Client-Secret: '.SEVENTH_TRADEHUB_CLIENT_SECRET,
            'X-7TH-Integration-Id: '.SEVENTH_TRADEHUB_INTEGRATION_ID,
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
 * @param  array<string, mixed>|null  $state
 */
function seventh_tradehub_render_shutdown(?array $state): void
{
    $hub = rtrim(SEVENTH_TRADEHUB_HUB_URL, '/');
    $status = strtolower((string) ($state['status'] ?? 'expired'));

    [$message, $label, $url] = match ($status) {
        'cancelled' => [
            'This website subscription has been cancelled. Contact 7th Trade Hub support for help.',
            'Open Help Center',
            $hub.'/help',
        ],
        'suspended' => [
            'This website has been suspended. Contact 7th Trade Hub support for help.',
            'Open Help Center',
            $hub.'/help',
        ],
        'inactive' => [
            'This website is inactive. Contact 7th Trade Hub support for help.',
            'Open Help Center',
            $hub.'/help',
        ],
        'pending_setup' => [
            'This website is not ready yet. Contact 7th Trade Hub support for help.',
            'Open Help Center',
            $hub.'/help',
        ],
        default => [
            'Your website subscription has expired. Sign in to your 7th Trade Hub account to renew this website subscription.',
            'Sign in to 7th Trade Hub',
            $hub.'/login',
        ],
    };

    header('HTTP/1.1 503 Service Unavailable');
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        .'<title>Subscription unavailable</title>'
        .'<style>body{font-family:system-ui,sans-serif;max-width:32rem;margin:4rem auto;padding:0 1rem;line-height:1.5;color:#111}'
        .'a{color:#0b57d0}</style></head><body>'
        .'<h1>Website unavailable</h1>'
        .'<p>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</p>'
        .'<p><a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener">'
        .htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'</a></p>'
        .'</body></html>';
}

/**
 * @return array<string, mixed>|null
 */
function seventh_tradehub_load_state(): ?array
{
    $file = SEVENTH_TRADEHUB_STATE_FILE;
    if (! is_file($file)) {
        return null;
    }

    $raw = file_get_contents($file);
    if ($raw === false) {
        return null;
    }

    $data = json_decode($raw, true);

    return is_array($data) ? $data : null;
}

/**
 * @param  array<string, mixed>  $state
 */
function seventh_tradehub_save_state(array $state): void
{
    $file = SEVENTH_TRADEHUB_STATE_FILE;
    $dir = dirname($file);
    if (! is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

/**
 * @param  array<string, mixed>  $body
 */
function seventh_tradehub_json_response(array $body, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($body);
    exit;
}

// Auto-handle Hub POSTs when this file is the request target:
if (PHP_SAPI !== 'cli') {
    seventh_tradehub_handle_hub_request();
}
