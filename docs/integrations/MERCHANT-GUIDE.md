# Merchant guide — integrate your website with 7th Trade Hub

This guide is for developers of independent websites. Follow it the same way you would integrate a payment gateway: configure credentials, implement endpoints, test, go live.

## 1. Receive credentials from Hub

A Hub operator creates either:

- **Demo integration** (shared demo host for a catalog product), or  
- **Owned tool** credentials after Setup on a customer purchase  

You receive:

| Key | Purpose |
| --- | ------- |
| `SEVENTH_TRADEHUB_INTEGRATION_ID` | UUID for this integration |
| `SEVENTH_TRADEHUB_CLIENT_ID` | Client id |
| `SEVENTH_TRADEHUB_CLIENT_SECRET` | HMAC secret (Hub signs / you verify) |
| `SEVENTH_TRADEHUB_WEBHOOK_SECRET` | Optional site→Hub webhook auth |
| `SEVENTH_TRADEHUB_HUB_URL` | Hub base URL, e.g. `https://7th-tradehub.online` |

See [samples/env.example](samples/env.example).

**Never mix demo and owned credentials.**

## 2. Identity rules

### Demo

Hub binds the signed identity to:

- Login as User → `demo_user_email`  
- Login as Admin → `demo_admin_email`  

### Owned tool

Hub binds Login as admin to **`user_tools.admin_email`** configured at Setup. The browser must not supply a trusted email.

## 3. Implement health

`POST {your-site}/api/7th-tradehub/v1/health`

- Verify Protocol v1 HMAC with your `client_secret` (see [PROTOCOL-v1.md](PROTOCOL-v1.md)).  
- Respond `200` with `{ "ok": true, "capabilities": [...] }`.  
- Hub Check Connection uses this URL derived from your base/site URL.

## 4. Implement consume (SSO)

Hub redirects the browser to:

```text
{your-site}/auth/7th-tradehub/demo/consume?token=...&integration_id=...
```

(Path is fixed for Protocol v1 for both demo and owned launches.)

**Required:** call Hub:

`POST {HUB}/api/site-integrations/v1/demo/tokens/validate`

Headers: `X-7TH-Client-Id`, `X-7TH-Client-Secret`  
Body: `{ "token": "..." }`

On `valid: true`, create a local session for `identity.email` only, then redirect to your dashboard/admin. Refuse if subscription is locally expired (owned).

Do **not** trust query `email` / `role` parameters from the browser.

## 5. Subscription (owned tools)

### Push

Hub POSTs signed body to:

`POST {your-site}/api/7th-tradehub/v1/subscription/sync`

Store `subscription.status` and `subscription.expires_at`. Prefer newer `expires_at` / `updated_at` — **never let an older `active` overwrite a newer `expired`.**

### Poll (required defense in depth)

Every few minutes:

`GET {HUB}/api/site-integrations/v1/subscription`  
Headers: `X-7TH-Client-Id`, `X-7TH-Client-Secret`, `X-7TH-Integration-Id`

If Hub reports expired (or `expires_at` past), shut down even if push was missed.

### Shutdown

When expired: block routes with maintenance UI, refuse new logins including SSO consume.

## 6. Optional webhook to Hub

`POST {HUB}/webhooks/site-integrations/{integration_id}`  
Header: `X-7TH-Webhook-Secret`  
No CSRF cookie required; secret authenticates the call.

## 7. Test with Hub

1. Operator runs **Check connection**.  
2. Demo: View Demo → Login as User/Admin.  
3. Owned: Setup → Login as admin from My Tools.  
4. Confirm expiry: poll returns expired after clock; site shuts down.

## 8. Rotate credentials

If Hub rotates keys, update your env immediately. Subscription expiry is **not** changed by rotation.

## Next

- [PROTOCOL-v1.md](PROTOCOL-v1.md)  
- [checklists/MERCHANT-GO-LIVE.md](checklists/MERCHANT-GO-LIVE.md)  
- [samples/php/](samples/php/)
