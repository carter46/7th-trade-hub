# Smoke test — Site Integration v1

Use this after implementing merchant endpoints. Replace placeholders with real values.

## Prerequisites

- HTTPS merchant site reachable from the public internet
- Credentials from Hub (demo or owned — never mixed on one deploy)
- Hub-bound user/admin emails exist on the merchant site
- `POST /api/7th-tradehub/v1/health` implemented with HMAC verify

---

## 1. Hub Check connection (operator)

**Who:** Hub operator in Admin → Demo Site Integrate, or Admin → User → Tools → Setup.

**Action:** Click **Check connection**.

**Pass:** Status OK / connection successful.

**Fail:** Fix health endpoint first (signature, `integration_id`, HTTP 200, `{ "ok": true }`).

---

## 2. Merchant health — local unit test

Run your HMAC verifier against a signed payload before going live. Use [php/protocol-v1-verify.php](php/protocol-v1-verify.php) or your port of it.

Minimum checks:

1. Valid signature → accept
2. Tampered body → reject
3. `expires_at` in the past → reject (see [PROTOCOL-v1.md](../PROTOCOL-v1.md#clock-skew))

Hub generates health assertions; you cannot curl health without computing the same HMAC.

---

## 3. SSO end-to-end

**Demo:** Hub product page → View Demo → Login as User / Admin.

**Owned:** My Tools → Login as admin (active subscription).

**Pass:** Browser lands on merchant site, session created, correct dashboard/admin.

**Fail common causes:**

- Token validate not called server-side
- Local user missing for Hub-bound email
- Wrong credentials (demo vs owned)
- Subscription locally expired (owned)

---

## 4. Optional webhook ping (merchant → Hub)

```bash
curl -sS -X POST "https://7th-tradehub.online/webhooks/site-integrations/YOUR_INTEGRATION_ID" \
  -H "Content-Type: application/json" \
  -H "X-7TH-Webhook-Secret: YOUR_WEBHOOK_SECRET" \
  -d '{"event":"ping"}'
```

**Pass:** HTTP 200 and `{ "ok": true }`.

### 4b. Optional owned — admin credential sync

When the local admin email or password changes (owned tools only). See [php/sync-admin-credentials.php](php/sync-admin-credentials.php).

**Pass:** HTTP 200 and `{ "ok": true }`. Connection status and keys on Hub must stay unchanged.

You cannot smoke-test this with a hand-written curl unless you compute the Protocol v1 HMAC. Use the PHP sample (or port `seventh_tradehub_sign`). Unsigned POSTs return **401** or **422**.

---

## 5. Owned only — subscription poll (cron)

Run your poll script or:

```bash
curl -sS "https://7th-tradehub.online/api/site-integrations/v1/subscription" \
  -H "Accept: application/json" \
  -H "X-7TH-Client-Id: YOUR_CLIENT_ID" \
  -H "X-7TH-Client-Secret: YOUR_CLIENT_SECRET" \
  -H "X-7TH-Integration-Id: YOUR_INTEGRATION_ID"
```

**Pass:** HTTP 200 with `status`, `expires_at`, `updated_at`.

Schedule every 5–15 minutes in production.

---

## 6. Owned only — expiry / Admin shutdown

After `expires_at` passes **or** Hub Admin clicks **Shutdown Site**:

- Poll returns `expired` (or past `expires_at`)
- Merchant site shows shutdown / session-expired UI for users and regular admins
- Login page/form still loads; only **super admin** may enter after password login
- Hub SSO consume refused
- Health and subscription sync still respond

Hub operator: ensure `site-integrations:expire-user-tools` runs every 5 minutes (`php artisan schedule:run`). After **Enable** (future expiry) or renew, site returns to normal.

---

## Quick reference

| Test | Who runs | Pass criteria |
| ---- | -------- | ------------- |
| Check connection | Hub operator | Hub UI OK |
| HMAC unit test | Merchant dev | Verify/reject as expected |
| SSO login | Both | Session on merchant site |
| Webhook ping | Merchant dev | 200 `{ "ok": true }` |
| Admin credential sync (owned, optional) | Merchant dev | 200 `{ "ok": true }`; keys unchanged |
| Subscription poll | Merchant cron | 200 snapshot JSON |

See [checklists/MERCHANT-GO-LIVE.md](../checklists/MERCHANT-GO-LIVE.md).
