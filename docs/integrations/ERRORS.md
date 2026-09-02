# Error codes — Site Integration Protocol v1

## Hub → merchant (validate / poll)

| HTTP | Meaning |
| ---- | ------- |
| 200 | Success |
| 401 | Invalid/missing client credentials |
| 404 | Unknown integration / tool |
| 422 | Invalid, expired, or already-used launch token |
| 429 | Rate limited |

## Merchant → Hub (webhook)

| HTTP | Meaning |
| ---- | ------- |
| 200 | Accepted (`{ "ok": true }`) |
| 401 | Invalid webhook secret |
| 404 | Unknown integration id |

## Hub Check Connection / sync failures (operator-facing)

| Message pattern | Cause |
| --------------- | ----- |
| Connection blocked: … must use HTTPS | Non-HTTPS URL |
| Connection blocked: … private or reserved | SSRF denied host/IP |
| Health check failed: HTTP … | Site returned non-success or `ok !== true` |
| Subscription sync failed | Site sync endpoint error |

## Merchant local failures (recommended)

| Condition | Behavior |
| --------- | -------- |
| Invalid signature on health/sync | 401; do not apply state |
| Missing/wrong `X-7TH-Client-Id` or `X-7TH-Integration-Id` | 401 |
| Expired assertion (`expires_at` past) | 401 |
| Unknown `integration_id` | 404 |
| Subscription expired | Refuse SSO; show shutdown UI |
| Stale sync (older than stored) | Ignore; keep newer state |
| Validate token missing/invalid | Do not create session; show error page |

## Recommended merchant error JSON (health / sync)

Hub **Check connection** only checks HTTP status and `"ok": true` on health. A consistent error body helps your logs and future Hub tooling.

**401 — invalid signature or expired assertion**

```json
{
  "ok": false,
  "error": "invalid_signature",
  "message": "HMAC verification failed."
}
```

```json
{
  "ok": false,
  "error": "assertion_expired",
  "message": "expires_at is in the past."
}
```

**401 — wrong integration or client headers**

```json
{
  "ok": false,
  "error": "unauthorized",
  "message": "Client id or integration id does not match."
}
```

**404 — unknown integration**

```json
{
  "ok": false,
  "error": "not_found",
  "message": "Unknown integration_id."
}
```

**200 success (health)**

```json
{
  "ok": true,
  "capabilities": ["health", "demo_user_login", "demo_admin_login"]
}
```

Subscription sync may return `{ "ok": true }` or an empty 200 body — Hub treats HTTP success as acceptance.

## Webhook events (site → Hub)

Protocol v1 documents **`ping`** only. Send `{ "event": "ping" }` to verify connectivity. Additional event types may be added in future protocol versions; unsupported events should return 422 with a clear message if you implement a generic receiver.
