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

## Scheduled poll (owned tools)

Schedule every 5–15 minutes:

`GET {HUB}/api/site-integrations/v1/subscription`

Headers: `X-7TH-Client-Id`, `X-7TH-Client-Secret`, `X-7TH-Integration-Id`.

See [../samples/php/poll-subscription.php](../samples/php/poll-subscription.php). Apply fail-closed shutdown when `status === expired` or `expires_at` is past.

## Full reference

[../ENDPOINTS-REFERENCE.md](../ENDPOINTS-REFERENCE.md)
