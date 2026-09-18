# Non-authenticated websites

Use this profile when the owned site has **no user/admin authentication** — brochure pages, marketing landings, WordPress marketing sites, simple PHP sites, CDN HTML, etc. Protocol v1 is the same as for application sites; only Hub capabilities and local enforcement differ.

## Hub setup

1. On **Admin → Users → Tools → Manage**, open **Initial setup** (or **Tool settings**).
2. Enter **Website URL**.
3. **Uncheck** “This website has admin authentication”.
4. Save & generate keys (or Save reconfiguration).
5. Copy Integration ID, Client ID, Client Secret, Webhook Secret, and Hub URL for the developer.

Hub will **not** store admin email/password and will **not** enable Auto Login. Capabilities: `health`, `subscription_sync`, `shutdown_on_expiry`.

## Status model (Hub push first)

```text
Hub pushes signed subscription/sync
  → site verifies HMAC
  → site stores local state
  → site gates the public site from that state
```

Merchants do **not** need a cron job on every site. Cron is optional.

### Fallback (throttled — not every visitor)

```text
Visitor
  → load local state
  → trustworthy/fresh?
       YES → serve/block from local state only
       NO  → if throttle allows: ONE Hub GET /subscription
              update last_reconciliation_attempt_at
              on success: update local state
           → serve/block from local state
```

Defaults (configurable in the sample):

| Constant | Default | Meaning |
| -------- | ------- | ------- |
| `MAX_TRUST_AGE` | 24 hours | After this, online local state is not trusted without Hub |
| `FALLBACK_RECONCILE_INTERVAL` | 15 minutes | Min time between Hub GET attempts |

### Online vs offline (enforcement)

Only Hub status **`active`** (and not past `expires_at` / trust age) is online. Everything else — including `pending_setup`, `expired`, `suspended`, `cancelled`, `inactive`, and unknown values — is offline.

When applying push or fallback snapshots, compare `subscription.updated_at` (and `expires_at` when timestamps tie). **Never** let an older `active` overwrite a newer offline status. The sample uses a file lock around fallback throttle so concurrent visitors do not stampede Hub.

Subscription sync payloads may omit `identity` when the tool has no admin email; do not require `identity.email` on `role=subscription`.

### Fail-closed when Hub is unreachable

| Condition | Behavior |
| --------- | -------- |
| Local status already offline | Stay offline |
| Status is not `active` | Offline |
| `expires_at` in the past | Offline even if status still says `active` |
| Online + `last_synced_at` within trust age + Hub down | Keep serving active (short window) |
| Online + `last_synced_at` older than trust age + Hub down | **Fail closed** (shutdown UI) |
| No local state + Hub down | **Fail closed** until first successful sync |

### Shutdown UI (everyone — no admin login)

| Hub `status` | Message | CTA |
| ------------ | ------- | --- |
| `expired` | Subscription expired — renew on 7th Trade Hub | `{HUB}/login` |
| `cancelled` | Subscription cancelled — contact support | `{HUB}/help` |
| `suspended` | Site suspended — contact support | `{HUB}/help` |
| `inactive` | Site inactive — contact support | `{HUB}/help` |

## Developer handoff checklist

1. Create / configure the owned tool in 7th Trade Hub (uncheck admin authentication).
2. Copy credentials (Integration ID, Client ID, Client Secret, Webhook secret, Hub URL).
3. Fill the config block in [samples/php/non-auth-site-gate.php](samples/php/non-auth-site-gate.php).
4. Include the gate at the site entry point (front controller, shared header, WP must-use plugin, etc.).
5. Expose `POST /api/7th-tradehub/v1/health` and `POST /api/7th-tradehub/v1/subscription/sync` (same script or thin router).
6. Run **Check connection** on Hub.
7. Test **Shutdown Site** → shutdown overlay from pushed status.
8. Test **Enable** → site returns to normal from pushed status.
9. (Optional) Simulate missed push: stale local `active` / past `expires_at` → confirm throttled reconcile or fail-closed.

## Sample

- Gate + health + sync: [samples/php/non-auth-site-gate.php](samples/php/non-auth-site-gate.php)
- HMAC helpers: [samples/php/protocol-v1-verify.php](samples/php/protocol-v1-verify.php)
- Optional fallback poll helper: [samples/php/poll-subscription.php](samples/php/poll-subscription.php)

Full protocol: [PROTOCOL-v1.md](PROTOCOL-v1.md) · Application sites: [MERCHANT-GUIDE.md](MERCHANT-GUIDE.md)
