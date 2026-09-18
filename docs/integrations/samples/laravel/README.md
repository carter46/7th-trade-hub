# Laravel sketch

Mount routes on **your merchant site**:

| Method | Path | Handler |
| ------ | ---- | ------- |
| `POST` | `/api/7th-tradehub/v1/health` | Verify signed body → `{ ok: true, capabilities: [...] }` |
| `GET` | `/auth/7th-tradehub/demo/consume` | Call Hub validate → local session → redirect |
| `POST` | `/api/7th-tradehub/v1/subscription/sync` | Verify signed body → update local subscription (owned) |

Store credentials in `.env` using [../env.example](../env.example).

## Consume controller

Use HTTP client with headers:

- `X-7TH-Client-Id`
- `X-7TH-Client-Secret`
- `Content-Type: application/json`
- `Accept: application/json`

POST to `{HUB}/api/site-integrations/v1/demo/tokens/validate` with `{ "token": "..." }`.

On success: verify `integration_id` matches env, load **existing** local user by `identity.email`, use validate `role` for redirect, skip password/MFA flows.

See [../samples/php/consume-validate.php](../samples/php/consume-validate.php).

## Health / sync middleware

Verify Protocol v1 HMAC using [../samples/php/protocol-v1-verify.php](../samples/php/protocol-v1-verify.php) (must match Hub `ProtocolV1Signer`).

Reject requests where `expires_at` is past or `integration_id` does not match env.

## Fallback reconciliation (owned tools)

Hub **pushes** `POST …/subscription/sync` as the primary path. Use Hub GET only when local state is missing/stale (throttled):

`GET {HUB}/api/site-integrations/v1/subscription`

Headers: `X-7TH-Client-Id`, `X-7TH-Client-Secret`, `X-7TH-Integration-Id`.

See [../samples/php/poll-subscription.php](../samples/php/poll-subscription.php) and [../NON-AUTHENTICATED-SITE.md](../NON-AUTHENTICATED-SITE.md). Apply fail-closed when `status` is offline, `expires_at` is past, or local sync is older than max trust age and Hub is unreachable. Authenticated sites: public session-expired UI; regular admin post-login Hub CTAs. Non-authenticated: [../samples/php/non-auth-site-gate.php](../samples/php/non-auth-site-gate.php).

## Admin email / password change (owned, optional)

When your local admin email or password is saved, POST to Hub using [../samples/php/sync-admin-credentials.php](../samples/php/sync-admin-credentials.php) (`seventh_tradehub_sign` in [../samples/php/protocol-v1-verify.php](../samples/php/protocol-v1-verify.php)). Do not rotate Hub keys. LiveChat credentials are not part of this event.

Call from your admin-profile / password-update controller after a successful local commit. Use a new `event_id` for each distinct change.

## Full reference

[../ENDPOINTS-REFERENCE.md](../ENDPOINTS-REFERENCE.md)
