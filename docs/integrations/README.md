# Site Integration Platform — Documentation

7th Trade Hub acts like a **payment gateway**: Hub owns APIs, signing, admin tooling, and this documentation. Independent websites (merchants) implement endpoints and config described here. Hub never needs your application source code.

> ### ⚠️ Protocol v1: URLs contain `/demo/` — including production
>
> These paths are **fixed for all integrations** (demo catalog **and** owned customer sites). **Do not rename them.**
>
> | Path | Used for |
> | ---- | -------- |
> | `/auth/7th-tradehub/demo/consume` | Browser SSO entry (demo + owned) |
> | `POST …/api/site-integrations/v1/demo/tokens/validate` | Server-side token validation (demo + owned) |
>
> The segment `demo` is a **Protocol v1 identifier**, not “non-production only.” Owned production sites use the same URLs.

**Protocol:** `7th-tradehub` v1 · **Docs updated:** see [CHANGELOG.md](CHANGELOG.md)

## Quick start (merchant developer)

1. Get credentials from Hub operator → [samples/env.example](samples/env.example)
2. Read [MERCHANT-GUIDE.md](MERCHANT-GUIDE.md)
3. Implement three endpoints on **your** site (see [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md)). Owned sites should also POST admin email/password changes to Hub ([samples/php/sync-admin-credentials.php](samples/php/sync-admin-credentials.php)).
4. Copy patterns from [samples/php/](samples/php/) · [samples/README.md](samples/README.md)
5. Go live with [checklists/MERCHANT-GO-LIVE.md](checklists/MERCHANT-GO-LIVE.md)
6. Smoke-test with [samples/SMOKE-TEST.md](samples/SMOKE-TEST.md)

**Browse online:** `https://7th-tradehub.online/developers/integrations` (same content as this folder).

**OpenAPI:** [openapi.yaml](openapi.yaml) · online: `/developers/integrations/openapi.yaml`

## Who should read what

| Audience | Start here |
| -------- | ---------- |
| External site developer | [MERCHANT-GUIDE.md](MERCHANT-GUIDE.md) → [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md) → [CONSUMER-PHP.md](CONSUMER-PHP.md) |
| Hub operator / catalog admin | [OPERATOR.md](OPERATOR.md) |
| Protocol details / signing | [PROTOCOL-v1.md](PROTOCOL-v1.md) |
| Machine-readable Hub APIs | [openapi.yaml](openapi.yaml) |
| Architecture overview | [OVERVIEW.md](OVERVIEW.md) |
| Error codes | [ERRORS.md](ERRORS.md) |
| Go-live checklist | [checklists/MERCHANT-GO-LIVE.md](checklists/MERCHANT-GO-LIVE.md) |
| Security checklist | [checklists/SECURITY.md](checklists/SECURITY.md) |
| Samples | [samples/README.md](samples/README.md) |

## FAQ

### Why do production owned tools use URLs with `demo` in the path?

Protocol v1 was named before owned-tool launches reused the same browser entrypoint. Hub redirects all SSO (demo and owned) to `/auth/7th-tradehub/demo/consume` and all token validation to `…/demo/tokens/validate`. Changing paths on the merchant site breaks Hub. A future protocol version may add aliases; v1 merchants must implement these exact paths.

### Do credentials have to live in `.env`?

No. Store them server-side wherever your app reads secrets (env, encrypted DB, admin settings). See [MERCHANT-GUIDE.md § Receive credentials](MERCHANT-GUIDE.md#1-receive-credentials-from-hub). Never expose secrets to the browser.

### What if SSO validates but login fails?

The email Hub binds must **already exist** on your site with the correct role. Hub does not create merchant users. See [MERCHANT-GUIDE.md § Identity](MERCHANT-GUIDE.md#3-identity-rules).

## Two integration contexts (never mix)

1. **Demo** — product-level `site_integrations` credentials and fixed `demo_user_email` / `demo_admin_email`.
2. **Owned tool** — per-purchase `user_tool_integrations` credentials and `user_tools.admin_email` from Setup.

## Protocol version

Current: **v1** (`protocol: 7th-tradehub`, `version: 1`). See [CHANGELOG.md](CHANGELOG.md).

## Hub API base path

All Hub endpoints documented as `/api/site-integrations/v1/...` are served under your Hub URL:

```text
{SEVENTH_TRADEHUB_HUB_URL}/api/site-integrations/v1/...
```

Webhooks:

```text
{SEVENTH_TRADEHUB_HUB_URL}/webhooks/site-integrations/{integration_id}
```

Ping uses the webhook secret only. Owned admin email/password updates use the same URL with a signed `owned.admin_credentials.updated` body (see [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md) and [samples/php/sync-admin-credentials.php](samples/php/sync-admin-credentials.php)). That call does not reconnect or rotate keys.
