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
