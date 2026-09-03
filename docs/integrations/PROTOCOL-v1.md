# 7th Trade Hub Integration Protocol v1

Independent websites stay on their own stack and database. 7th Trade Hub orchestrates **demo access** and **purchased tool provisioning** through a shared signed protocol.

## Credential split (non-negotiable)

| Layer | Table | Used for |
| ----- | ----- | -------- |
| Demo | `site_integrations` | Pre-purchase View Demo on a catalog product |
| Owned | `user_tool_integrations` | Customer purchased instance (My Tools) |

A purchased site must **never** use the product’s demo secrets.

## Assertion envelope

Every signed Hub→site request includes:

```json
{
  "protocol": "7th-tradehub",
  "version": 1,
  "integration_id": "...",
  "context": "demo",
  "role": "admin",
  "identity": { "email": "demo-admin@example.com" },
  "request_id": "...",
  "nonce": "...",
  "issued_at": "...",
  "expires_at": "...",
  "signature": "..."
}
```

`context` is `demo` or `owned_tool`. Subscription payloads also include a `subscription` object.

Site→Hub **admin credential sync** uses the same envelope (`role`: `credential_sync`). `identity` is omitted on password-only updates; `credential.password` is omitted on email-only updates. Include at least one.

## Signing model (normative)

Implemented by Hub `ProtocolV1Signer`:

1. Payload includes all fields **except** `signature`.
2. Remove `signature` if present; `ksort` top-level keys.
3. Canonicalize recursively:
   - Objects: `{` + sorted `key:value` pairs joined by `,` + `}` where keys/values are canonicalized.
   - Lists: `[` + items + `]`.
   - Strings: JSON-style quoted with escapes for `\`, `"`, newlines, tabs.
   - Bools: `true` / `false`; null: `null`; numbers: decimal string.
4. `signature = HMAC-SHA256(canonical, client_secret)` as lowercase hex.
5. Verify with `hash_equals`.

Replay: reject assertions whose `expires_at` is past. Prefer also tracking `nonce` / `request_id` locally.

**There is no global Hub secret** shared across all sites.

## Identity binding

- Launch URLs must **not** accept a trusted `email` query parameter.
- Hub embeds the allowed email in the launch token record / validate response:
  - Demo user → `site_integrations.demo_user_email`
  - Demo admin → `site_integrations.demo_admin_email`
  - Owned admin → `user_tools.admin_email` from Setup, Reconfigure, or optional site→Hub `owned.admin_credentials.updated`
- **Merchants must pre-create** local user records for those emails with appropriate roles. Hub never provisions users on merchant sites.
- Owned integrations support **admin SSO only** (no owned user launch).

## Browser launch flow

1. Authenticated Hub user clicks Login as User / Admin (demo) or Login as admin (My Tools).
2. Hub stores a one-time token **hash** and redirects to:

   `{site}/auth/7th-tradehub/demo/consume?token=...&integration_id=...`

3. Site **must** call Hub (redirect does not include a full signed assertion):

   `POST /api/site-integrations/v1/demo/tokens/validate`  
   Headers: `X-7TH-Client-Id`, `X-7TH-Client-Secret`

4. Site creates a local session for `identity.email`, using `role` from the validate response for redirect (not query params). Skip password/MFA/onboarding — SSO is server-validated only.
5. Redirect to merchant dashboard (user) or admin area (admin).

## Site endpoints to implement

| Endpoint | Purpose |
| -------- | ------- |
| `POST /api/7th-tradehub/v1/health` | Connection check — verify signed Hub POST |
| `GET /auth/7th-tradehub/demo/consume` | Auto-login entry (demo **and** owned) |
| `POST /api/7th-tradehub/v1/subscription/sync` | Receive Hub subscription push (owned only) |

See [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md) for headers, bodies, and responses.

### Hub → site request headers

When Hub calls your health or subscription/sync endpoints:

| Header | Purpose |
| ------ | ------- |
| `Content-Type` | `application/json` |
| `X-7TH-Client-Id` | Your client id |
| `X-7TH-Integration-Id` | Your integration UUID |

Body is a full signed Protocol v1 assertion (including `signature`).

### Health assertion fields

- `context`: `demo` or `owned_tool`
- `role`: `health`
- `identity.email`: `health@7th-tradehub.local` (informational)
- `expires_at`: reject if past (Hub uses ~2 minute window)

### Subscription sync assertion fields

- `context`: `owned_tool`
- `role`: `subscription`
- `subscription`: `{ tool_id, public_id, status, expires_at, updated_at }`

## Hub endpoints for sites

| Endpoint | Purpose |
| -------- | ------- |
| `POST /api/site-integrations/v1/demo/tokens/validate` | Consume one-time token |
| `GET /api/site-integrations/v1/subscription` | Poll subscription |
| `POST /webhooks/site-integrations/{integration_id}` | Optional ping (`X-7TH-Webhook-Secret` only) **or** owned `owned.admin_credentials.updated` (secret + client id + Protocol v1 signature). CSRF exempt. Does not change connection/keys. |

Hub `/api/site-integrations/v1/*` routes are rate-limited to **60 requests/minute** per IP.

### Launch token rules

- Plain token in redirect URL; Hub stores **SHA-256 hash** only.
- TTL: **120 seconds** from issue.
- Single use — second validate returns 422.
- Validate API works for demo **and** owned tokens when correct client credentials are supplied.

### Validate response (success)

```json
{
  "valid": true,
  "protocol": "7th-tradehub",
  "version": 1,
  "context": "demo",
  "role": "admin",
  "identity": { "email": "…" },
  "integration_id": "…",
  "expires_at": "…"
}
```

## Capabilities

Return the flags your site actually supports. Hub **Check connection** only requires HTTP 200 and `"ok": true`; capabilities are informational for operators and future Hub features.

| Capability | Demo | Owned | Meaning |
| ------------ | ---- | ----- | ------- |
| `health` | ✓ | ✓ | Health endpoint implemented |
| `demo_user_login` | ✓ | — | SSO for demo user role |
| `demo_admin_login` | ✓ | — | SSO for demo admin role |
| `subscription_sync` | — | ✓ | Push sync endpoint implemented |
| `shutdown_on_expiry` | — | ✓ | Site shuts down when subscription expires |
| `owned_admin_login` | — | ✓ | SSO for purchased tool admin |
| `admin_credential_sync` | — | ✓ | Site pushes admin email/password changes to Hub (optional; omit until implemented) |

**Demo example:** `["health", "demo_user_login", "demo_admin_login"]`  
**Owned example:** `["health", "subscription_sync", "shutdown_on_expiry", "owned_admin_login"]`  
**Owned with credential sync:** add `"admin_credential_sync"` only after you POST `owned.admin_credentials.updated` from your admin-email/password change handlers.

Omitting a capability does not fail Check connection today, but misreporting (e.g. claiming `subscription_sync` without an endpoint) will break Setup/sync later.

### Site → Hub admin credential sync

Owned sites should notify Hub when the **local admin identity** used for SSO / Copy password changes. Hook this **after** the local admin email or password is committed on your site.

Call Hub on:

- admin email change (keep a local user with the new email so Auto Login still works)
- admin password change (Hub stores it encrypted for My Tools Copy password only — Auto Login does not use the password)

Do **not** POST LiveChat credentials. Do **not** rotate Hub keys or wait for Check connection.

**Endpoint:** `POST {SEVENTH_TRADEHUB_HUB_URL}/webhooks/site-integrations/{integration_id}`  
**CSRF:** none (this URL is exempt). Rate limit: 60/min.

**Headers (all required for this event):**

| Header | Env |
| ------ | --- |
| `Content-Type` | `application/json` |
| `X-7TH-Webhook-Secret` | `SEVENTH_TRADEHUB_WEBHOOK_SECRET` |
| `X-7TH-Client-Id` | `SEVENTH_TRADEHUB_CLIENT_ID` |

Sign the JSON body with `SEVENTH_TRADEHUB_CLIENT_SECRET` using [samples/php/protocol-v1-verify.php](samples/php/protocol-v1-verify.php) (`seventh_tradehub_sign`) — the same canonical HMAC as health. Copy-paste implementation: [samples/php/sync-admin-credentials.php](samples/php/sync-admin-credentials.php).

**Required body fields**

| Field | Value |
| ----- | ----- |
| `protocol` | `7th-tradehub` (added by the signer) |
| `version` | `1` (added by the signer) |
| `integration_id` | Same UUID as the URL and `SEVENTH_TRADEHUB_INTEGRATION_ID` |
| `context` | `owned_tool` |
| `role` | `credential_sync` |
| `event` | `owned.admin_credentials.updated` |
| `event_id` | Unique per change (string, max 64). Reuse only to retry the **same** change. Hub returns `{ "ok": true, "deduped": true }` and does not apply twice. |
| `request_id` | Unique request id (string) |
| `nonce` | Unique nonce (string) |
| `issued_at` | ISO-8601 timestamp |
| `expires_at` | ISO-8601, a few minutes in the future (~2–5 minutes). Hub rejects past expiry. |
| `signature` | HMAC-SHA256 hex from the signer |
| `identity.email` and/or `credential.password` | At least one. Email is lowercased. Password 6–255 characters. |

**Email-only example** (password omitted on purpose):

```json
{
  "protocol": "7th-tradehub",
  "version": 1,
  "integration_id": "11111111-1111-1111-1111-111111111111",
  "context": "owned_tool",
  "role": "credential_sync",
  "event": "owned.admin_credentials.updated",
  "event_id": "a1b2c3d4e5f6789012345678abcdef01",
  "request_id": "…",
  "nonce": "…",
  "issued_at": "2026-09-03T19:30:00+00:00",
  "expires_at": "2026-09-03T19:33:00+00:00",
  "identity": { "email": "new-admin@example.com" },
  "signature": "…"
}
```

**Password-only:** omit `identity`, set `"credential": { "password": "NewPass456!" }`.  
**Both:** include `identity` and `credential`.

Hub updates only `user_tools.admin_email` / encrypted `user_tools.admin_password`. **No reconnect, no key rotation, no health check, no subscription change.**

- Auto Login binds SSO to Hub’s stored email after a successful email sync.
- Copy password on My Tools returns the last password Hub stored (Setup, Reconfigure, or this event).
- Demo integrations receive **403**. Sites that never send this event stay connected.
- Advertise capability `admin_credential_sync` on health **only after** you actually POST this event from your change handlers. Hub does not require the flag to accept the webhook.

**Retries:** on HTTP 5xx or network failure, retry the same payload (same `event_id`) or sign a new payload with a new `event_id` for the same email/password. After HTTP 200, use a **new** `event_id` for a later change.

See [ENDPOINTS-REFERENCE.md § 5b](ENDPOINTS-REFERENCE.md#5b-owned-admin-credential-sync-additive).

## Clock skew

Treat `expires_at` as expired when `expires_at < now()` on the merchant server.

- **No grace window** in Protocol v1 — do not add ±30s tolerance unless you document it for your own app only; Hub assertions use short TTLs (~2 minutes for health).
- Keep merchant and Hub servers on NTP; clock drift causes false rejections on health/sync and token validate failures.

## Subscription expiry (defense in depth)

1. Hub treats `expires_at < now()` as expired for launch and poll **immediately** (does not wait for cron).
2. Scheduled job marks stored status `expired` and pushes sync (with row locks against renew races).
3. Hub Admin **Shutdown Site** also sets `expired` + `expires_at=now()` and pushes the same sync (no separate event).
4. Site stores local subscription state; **periodically polls** Hub.
5. Site shuts down when expired even if a Hub push failed — except the **login page/form**. After password login, only a **super admin** (upgraded existing admin on the merchant site) may enter; users and regular admins see the same session-expired UI. Refuse Hub SSO while expired.
6. When applying sync messages, compare `subscription.expires_at` / `updated_at` — never let an older `active` overwrite a newer `expired`.

## Environment variables (site side)

```
SEVENTH_TRADEHUB_INTEGRATION_ID=
SEVENTH_TRADEHUB_CLIENT_ID=
SEVENTH_TRADEHUB_CLIENT_SECRET=
SEVENTH_TRADEHUB_WEBHOOK_SECRET=
SEVENTH_TRADEHUB_HUB_URL=https://your-hub.example
```
