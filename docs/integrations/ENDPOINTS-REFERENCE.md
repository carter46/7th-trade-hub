# Endpoint reference — Hub APIs and merchant endpoints

Use this as a quick lookup while implementing. Normative signing rules: [PROTOCOL-v1.md](PROTOCOL-v1.md).

**Hub base URL:** `SEVENTH_TRADEHUB_HUB_URL` (e.g. `https://7th-tradehub.online`)  
**All Hub API paths below are prefixed with `/api`.**

---

## Merchant site — you implement

| Method | Path | Purpose |
| ------ | ---- | ------- |
| `POST` | `/api/7th-tradehub/v1/health` | Hub connection check |
| `GET` | `/auth/7th-tradehub/demo/consume` | Browser SSO entry (demo **and** owned) |
| `POST` | `/api/7th-tradehub/v1/subscription/sync` | Hub subscription push (owned only) |

Your `base_url` / `site_url` must be **HTTPS** and publicly reachable (no localhost/private IPs when Hub calls you).

**Fixed paths:** Hub uses the paths above literally. Wire your router, rewrite rules, or file layout so these URLs reach your handlers. See [MERCHANT-GUIDE.md § Exact paths](MERCHANT-GUIDE.md#2-exact-paths-and-routing).

**During shutdown:** Keep `POST …/health` (and owned `POST …/subscription/sync`) responding so Hub can run Check connection and push expiry updates even when customers see a maintenance page.

---

## Hub — you call

| Method | Path | Purpose |
| ------ | ---- | ------- |
| `POST` | `/api/site-integrations/v1/demo/tokens/validate` | Consume one-time launch token |
| `GET` | `/api/site-integrations/v1/subscription` | Poll owned-tool subscription |
| `POST` | `/webhooks/site-integrations/{integration_id}` | Optional ping to Hub |

Rate limit: **60 requests/minute** per IP on `/api/site-integrations/v1/*`.

---

## 1. Health — `POST /api/7th-tradehub/v1/health`

Hub sends a signed Protocol v1 JSON body.

**Request headers**

| Header | Required | Notes |
| ------ | -------- | ----- |
| `Content-Type` | yes | `application/json` |
| `Accept` | yes | `application/json` |
| `X-7TH-Client-Id` | yes | Your client id |
| `X-7TH-Integration-Id` | yes | Your integration UUID |

**Request body (example)**

```json
{
  "protocol": "7th-tradehub",
  "version": 1,
  "integration_id": "550e8400-e29b-41d4-a716-446655440000",
  "context": "demo",
  "role": "health",
  "identity": { "email": "health@7th-tradehub.local" },
  "request_id": "…",
  "nonce": "…",
  "issued_at": "2026-09-02T10:00:00+00:00",
  "expires_at": "2026-09-02T10:02:00+00:00",
  "signature": "…"
}
```

`context` is `demo` or `owned_tool` depending on integration type.

**Your handler must**

1. Verify HMAC signature with your `SEVENTH_TRADEHUB_CLIENT_SECRET`.
2. Reject if `expires_at` is in the past.
3. Confirm `integration_id` matches your env.
4. Return **HTTP 200** with JSON:

```json
{
  "ok": true,
  "capabilities": ["health", "demo_user_login", "demo_admin_login"]
}
```

Return your actual supported capabilities (see [PROTOCOL-v1.md § Capabilities](PROTOCOL-v1.md#capabilities)). Any non-200 or `ok !== true` fails Hub **Check connection**.

**Recommended error responses:** [ERRORS.md § Merchant error JSON](ERRORS.md#recommended-merchant-error-json-health--sync)

**Clock skew:** reject when `expires_at` is past — no grace window; use NTP ([PROTOCOL-v1.md § Clock skew](PROTOCOL-v1.md#clock-skew)).

---

## 2. Consume — `GET /auth/7th-tradehub/demo/consume`

Hub redirects the user's browser:

```text
{your-site}/auth/7th-tradehub/demo/consume?token=…&integration_id=…
```

**Do not trust** query parameters for identity. The path name includes `demo` for Protocol v1 but is used for **owned admin launches** too (there is no owned “login as user” from Hub).

**Required server-side steps**

1. Read `token` and `integration_id` from the query string.
2. `POST {HUB}/api/site-integrations/v1/demo/tokens/validate` with:

   | Header | Value |
   | ------ | ----- |
   | `Content-Type` | `application/json` |
   | `Accept` | `application/json` |
   | `X-7TH-Client-Id` | Your client id |
   | `X-7TH-Client-Secret` | Your client secret |

   Body: `{ "token": "…" }`

3. On **HTTP 200** and `"valid": true`:
   - Confirm validate response `integration_id` matches `SEVENTH_TRADEHUB_INTEGRATION_ID` (and query `integration_id` if present).
   - Load local user by `identity.email` — user **must already exist** on your site; Hub does not provision accounts.
   - Use validate response **`role`** (`user` or `admin`) for redirect; optionally verify local user role matches.
   - Create session server-side without password/MFA/onboarding flows.
4. Redirect to your user dashboard or admin area based on `role`.
5. For **owned** tools: refuse login if your local subscription state is expired.

**Launch token lifetime:** 120 seconds from issue; single use.

**Validate success response (200)**

```json
{
  "valid": true,
  "protocol": "7th-tradehub",
  "version": 1,
  "context": "demo",
  "role": "admin",
  "identity": { "email": "demo-admin@example.com" },
  "integration_id": "550e8400-e29b-41d4-a716-446655440000",
  "expires_at": "2026-09-02T10:02:00+00:00"
}
```

| HTTP | Meaning |
| ---- | ------- |
| 401 | Wrong/missing `X-7TH-Client-Id` or `X-7TH-Client-Secret` |
| 422 | Invalid, expired, or already-used token (`valid: false` in body) |

Works with **demo** or **owned** credentials — use the secret that matches the integration receiving the redirect.

---

## 3. Subscription sync — `POST /api/7th-tradehub/v1/subscription/sync`

Owned tools only. Hub pushes after Setup, renew, expiry job, etc.

**Request headers:** same pattern as health (`X-7TH-Client-Id`, `X-7TH-Integration-Id`).

**Request body:** signed assertion with `role: "subscription"` and:

```json
{
  "subscription": {
    "tool_id": 42,
    "public_id": "…",
    "status": "active",
    "expires_at": "2026-12-02T10:00:00+00:00",
    "updated_at": "2026-09-02T10:00:00+00:00"
  }
}
```

**Status values:** `pending_setup`, `active`, `suspended`, `expired` (Hub may report `expired` when `expires_at` is past even if stored status lagged).

**Your handler must**

1. Verify signature.
2. Apply monotonic update: if incoming `subscription.updated_at` / `expires_at` is **older** than what you stored, ignore (never let stale `active` overwrite newer `expired`).
3. Return HTTP 200 (body format is up to you; Hub checks HTTP success only).

---

## 4. Poll subscription — `GET /api/site-integrations/v1/subscription`

Owned tools only. Run every **5–15 minutes** via cron.

**Request headers**

| Header | Required |
| ------ | -------- |
| `Accept` | `application/json` |
| `X-7TH-Client-Id` | yes |
| `X-7TH-Client-Secret` | yes |
| `X-7TH-Integration-Id` | yes (owned integration UUID) |

Optional query fallback: `?integration_id=…` if header omitted.

**Success response (200)**

```json
{
  "protocol": "7th-tradehub",
  "version": 1,
  "tool_id": 42,
  "public_id": "…",
  "status": "active",
  "expires_at": "2026-12-02T10:00:00+00:00",
  "updated_at": "2026-09-02T10:00:00+00:00"
}
```

If `status` is `expired` or `expires_at` is in the past → shut down locally even if sync push failed.

| HTTP | Meaning |
| ---- | ------- |
| 401 | Invalid credentials |
| 404 | Unknown integration / tool |

---

## 5. Webhook to Hub — `POST /webhooks/site-integrations/{integration_id}`

Optional connectivity ping.

**Headers:** `X-7TH-Webhook-Secret: {SEVENTH_TRADEHUB_WEBHOOK_SECRET}`  
**Body (example):** `{ "event": "ping" }`  
No session cookie / CSRF required.

**Response:** `{ "ok": true }` on success.

---

## Credential contexts

| Context | When you get credentials | Table (Hub internal) |
| ------- | ------------------------ | -------------------- |
| Demo | Hub operator creates Demo Site Integrate for a Website Package product | `site_integrations` |
| Owned | After customer/admin **Setup** on a purchased tool in My Tools | `user_tool_integrations` |

Never use demo credentials for a production customer site.
