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
  - Owned admin → `user_tools.admin_email` from Setup

## Browser launch flow

1. Authenticated Hub user clicks Login as User / Admin (demo) or Login as admin (My Tools).
2. Hub stores a one-time token **hash** and redirects to:

   `{site}/auth/7th-tradehub/demo/consume?token=...&integration_id=...`

3. Site **must** call Hub (redirect does not include a full signed assertion):

   `POST /api/site-integrations/v1/demo/tokens/validate`  
   Headers: `X-7TH-Client-Id`, `X-7TH-Client-Secret`

4. Site creates a local session for `identity.email` and redirects to `/dashboard` or `/admin`.

## Site endpoints to implement

| Endpoint | Purpose |
| -------- | ------- |
| `POST /api/7th-tradehub/v1/health` | Connection check |
| `GET /auth/7th-tradehub/demo/consume` | Auto-login entry |
| `POST /api/7th-tradehub/v1/subscription/sync` | Receive Hub subscription push (owned) |

## Hub endpoints for sites

| Endpoint | Purpose |
| -------- | ------- |
| `POST /api/site-integrations/v1/demo/tokens/validate` | Consume one-time token |
| `GET /api/site-integrations/v1/subscription` | Poll subscription |
| `POST /webhooks/site-integrations/{integration_id}` | Optional site→Hub ping (`X-7TH-Webhook-Secret`; CSRF exempt) |

## Capabilities

- Demo: `health`, `demo_user_login`, `demo_admin_login`
- Owned: `health`, `subscription_sync`, `shutdown_on_expiry`, `owned_admin_login`

## Subscription expiry (defense in depth)

1. Hub treats `expires_at < now()` as expired for launch and poll **immediately** (does not wait for cron).
2. Scheduled job marks stored status `expired` and pushes sync (with row locks against renew races).
3. Site stores local subscription state; **periodically polls** Hub.
4. Site shuts down when expired even if a Hub push failed.
5. When applying sync messages, compare `subscription.expires_at` / `updated_at` — never let an older `active` overwrite a newer `expired`.

## Environment variables (site side)

```
SEVENTH_TRADEHUB_INTEGRATION_ID=
SEVENTH_TRADEHUB_CLIENT_ID=
SEVENTH_TRADEHUB_CLIENT_SECRET=
SEVENTH_TRADEHUB_WEBHOOK_SECRET=
SEVENTH_TRADEHUB_HUB_URL=https://your-hub.example
```
