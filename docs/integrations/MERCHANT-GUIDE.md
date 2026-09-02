# Merchant guide — integrate your website with 7th Trade Hub

This guide is for developers of **independent websites** (merchants). Integrate the same way you would a payment gateway: configure credentials, implement endpoints, test with Hub, go live.

**Quick lookup:** [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md)  
**Signing details:** [PROTOCOL-v1.md](PROTOCOL-v1.md)  
**Sample code:** [samples/php/](samples/php/)  
**Online docs:** `{HUB}/developers/integrations` (e.g. `https://7th-tradehub.online/developers/integrations`)

> **Protocol v1 path note:** URLs contain `/demo/` (e.g. `/auth/7th-tradehub/demo/consume` and `/api/site-integrations/v1/demo/tokens/validate`) for **both demo and owned** integrations. Do not rename them.

---

## 1. Receive credentials from Hub

A Hub operator creates either:

- **Demo integration** — shared demo host for a Website Package catalog product (Admin → Demo Site Integrate), or
- **Owned tool credentials** — generated when a customer (or admin) completes **Setup** on a purchased tool in My Tools.

You receive (copy once — secrets are not shown again):

| Env variable | Purpose |
| ------------ | ------- |
| `SEVENTH_TRADEHUB_INTEGRATION_ID` | UUID for this integration |
| `SEVENTH_TRADEHUB_CLIENT_ID` | Client id |
| `SEVENTH_TRADEHUB_CLIENT_SECRET` | HMAC secret — verify Hub requests & authenticate to Hub |
| `SEVENTH_TRADEHUB_WEBHOOK_SECRET` | Optional — authenticate site→Hub webhook |
| `SEVENTH_TRADEHUB_HUB_URL` | Hub base URL, e.g. `https://7th-tradehub.online` |

See [samples/env.example](samples/env.example).

**Never mix demo and owned credentials.** Each customer site gets its own owned keys at Setup.

**Site URL requirements:** HTTPS only. Hub rejects localhost, private IPs, and non-public hosts when calling your site.

---

## 2. Exact paths and routing

Protocol v1 uses **fixed URL paths**. Hub redirects and connection checks target these literally — you cannot change them without breaking integration.

| Method | Path (on your site) |
| ------ | ------------------- |
| `POST` | `/api/7th-tradehub/v1/health` |
| `GET` | `/auth/7th-tradehub/demo/consume` |
| `POST` | `/api/7th-tradehub/v1/subscription/sync` |

How you wire these depends on your stack:

- **Laravel / Express / Rails:** register routes for the exact paths above.
- **Apache:** map paths to PHP handlers or front controller (`RewriteRule`).
- **nginx:** `location` blocks or rewrite to your handler.
- **Flat PHP:** place files at matching paths (e.g. `auth/7th-tradehub/demo/consume.php`) or route through `index.php`.

The `/auth/…/demo/consume` segment is required even for **owned** production sites.

**Health and subscription sync** should remain reachable when your site shows a customer-facing maintenance or shutdown UI — Hub **Check connection** and expiry pushes call these from Hub servers.

---

## 3. Identity rules

### Demo

Hub binds SSO to fixed emails configured in Demo Site Integrate:

- Login as User → `demo_user_email`
- Login as Admin → `demo_admin_email`

### Owned tool

Hub binds **Login as admin** to `user_tools.admin_email` entered at Setup. The browser must not supply a trusted email.

**Owned tools support admin SSO only** — there is no “login as user” launch from Hub for purchased instances.

### Local users must exist before SSO works

Hub **does not create users** on your site. The email Hub binds must already exist in **your** database with the correct role:

| Hub action | Email source | Your site must have |
| ---------- | ------------ | ------------------- |
| Demo → Login as User | `demo_user_email` | User with that email (non-admin or user role, per your app) |
| Demo → Login as Admin | `demo_admin_email` | User with that email and admin privileges |
| Owned → Login as admin | Setup `admin_email` | User with that email and admin privileges |

If the email is missing, token validation succeeds but your login step fails. **Pre-create** these accounts (or have a controlled provisioning process) before testing SSO.

Coordinate with the Hub operator so configured emails match real accounts on your site.

---

## 4. Implement health

`POST {your-site}/api/7th-tradehub/v1/health`

Hub sends a **signed JSON body** (Protocol v1) with headers:

- `X-7TH-Client-Id`
- `X-7TH-Integration-Id`
- `Content-Type: application/json`

Your handler must:

1. Verify HMAC with `SEVENTH_TRADEHUB_CLIENT_SECRET` ([samples/php/protocol-v1-verify.php](samples/php/protocol-v1-verify.php)).
2. Reject expired assertions (`expires_at` in the past).
3. Return HTTP **200** with `{ "ok": true, "capabilities": [...] }`.

Full request/response: [ENDPOINTS-REFERENCE.md § Health](ENDPOINTS-REFERENCE.md#1-health--post-api7th-tradehubv1health).

---

## 5. Implement consume (SSO)

Hub redirects the browser to:

```text
{your-site}/auth/7th-tradehub/demo/consume?token=...&integration_id=...
```

Same path for demo **and** owned launches (Protocol v1).

**Required:** server-side call to Hub (do not trust query `email` / `role`):

```http
POST {HUB}/api/site-integrations/v1/demo/tokens/validate
Content-Type: application/json
Accept: application/json
X-7TH-Client-Id: {your client id}
X-7TH-Client-Secret: {your client secret}

{"token":"..."}
```

On HTTP 200 and `"valid": true`:

1. Confirm `integration_id` in the validate response matches `SEVENTH_TRADEHUB_INTEGRATION_ID` (and optionally matches the query `integration_id`).
2. Confirm the local user for `identity.email` exists and is allowed to sign in.
3. Use **`role` from the validate response** (`user` or `admin`) for post-login redirect — not query parameters.
4. Optionally verify the local user’s role matches Hub’s `role` (e.g. do not grant admin UI to a non-admin DB user when `role` is `admin`).
5. Create a **server-side session** directly — do **not** route through password login, MFA/2FA, or first-login onboarding flows. Hub SSO is a trusted server-to-server validated entry.
6. Redirect to your user dashboard or admin area based on `role`.

- Token lifetime: **120 seconds**, single use.
- Sample: [samples/php/consume-validate.php](samples/php/consume-validate.php).
- For owned tools: refuse login if subscription is locally expired.

---

## 6. Subscription (owned tools only)

### Push — Hub → you

`POST {your-site}/api/7th-tradehub/v1/subscription/sync`

Signed body includes `subscription.status`, `subscription.expires_at`, `subscription.updated_at`.

Store locally. When applying updates, prefer newer `updated_at` / `expires_at` — **never let an older `active` overwrite a newer `expired`.**

### Poll — you → Hub (required)

Every **5–15 minutes** (cron):

```http
GET {HUB}/api/site-integrations/v1/subscription
Accept: application/json
X-7TH-Client-Id: ...
X-7TH-Client-Secret: ...
X-7TH-Integration-Id: ...
```

Sample: [samples/php/poll-subscription.php](samples/php/poll-subscription.php).

If Hub reports `expired` or `expires_at` is past → shut down even if push was missed.

### Shutdown

When expired: maintenance UI, block routes, refuse new logins including SSO consume.

---

## 7. Optional webhook to Hub

```http
POST {HUB}/webhooks/site-integrations/{integration_id}
X-7TH-Webhook-Secret: {SEVENTH_TRADEHUB_WEBHOOK_SECRET}
Content-Type: application/json

{"event":"ping"}
```

No CSRF cookie; secret authenticates the call. Response: `{ "ok": true }`.

---

## 8. Test with Hub

1. Operator runs **Check connection** (Admin → Demo Site Integrate, or admin user Tools tab for owned).
2. **Demo:** View Demo → Login as User / Admin on product page.
3. **Owned:** My Tools → Setup → Login as admin.
4. Confirm expiry: after `expires_at`, poll returns `expired` and site shuts down.

---

## 9. Rotate credentials

If Hub rotates keys, update your stored secrets immediately. Subscription expiry is **not** reset by rotation.

For owned tools, **Reconfigure** updates URLs/email/password without new keys; **Rotate credentials** issues new client/webhook secrets without extending subscription.

### Rotation without downtime (recommended)

1. Hub operator clicks **Rotate keys** (or owned **Rotate credentials**) and copies the new Client Secret / Webhook Secret.
2. Merchant updates secrets in env, DB, or admin settings — **keep the old secret available briefly** if your deploy is rolling.
3. Merchant verifies health locally (HMAC still works with new secret).
4. Hub operator runs **Check connection** — must pass with new secrets active on the merchant site.
5. Remove old secret from merchant storage once Check connection passes.
6. Confirm SSO (consume → validate) still works — validate uses the same client id/secret headers.

**Note:** During a brief overlap window, Hub only accepts the **current** secret on file. Update the merchant site **before** or **immediately when** rotating on Hub to avoid failed checks and SSO.

---

## Implementation checklist

| Step | Done |
| ---- | ---- |
| Env vars set server-side only | ☐ |
| Demo vs owned credentials separated | ☐ |
| `POST …/health` verifies HMAC + returns `ok: true` | ☐ |
| `GET …/demo/consume` calls Hub validate | ☐ |
| `integration_id` from validate matches env | ☐ |
| Session uses `identity.email` from validate only | ☐ |
| Post-login redirect uses validate `role` | ☐ |
| Hub-bound emails pre-exist locally with correct roles | ☐ |
| SSO bypasses password/MFA/onboarding flows | ☐ |
| Health responds during customer maintenance mode | ☐ |
| `POST …/subscription/sync` verifies HMAC (owned) | ☐ |
| Poll cron configured (owned) | ☐ |
| Expired → fail-closed shutdown | ☐ |
| Hub Check connection passes | ☐ |

Also see [checklists/MERCHANT-GO-LIVE.md](checklists/MERCHANT-GO-LIVE.md).

---

## Next

- [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md)
- [PROTOCOL-v1.md](PROTOCOL-v1.md)
- [openapi.yaml](openapi.yaml)
- [OPERATOR.md](OPERATOR.md) (Hub-side setup)
