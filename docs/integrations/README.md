# Site Integration Platform — Documentation

7th Trade Hub acts like a **payment gateway**: Hub owns APIs, signing, admin tooling, and this documentation. Independent websites (merchants) implement endpoints and config described here. Hub never needs your application source code.

## Quick start (merchant developer)

1. Get credentials from Hub operator → [samples/env.example](samples/env.example)
2. Read [MERCHANT-GUIDE.md](MERCHANT-GUIDE.md)
3. Implement three endpoints on **your** site (see [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md))
4. Copy patterns from [samples/php/](samples/php/)
5. Go live with [checklists/MERCHANT-GO-LIVE.md](checklists/MERCHANT-GO-LIVE.md)

## Who should read what

| Audience | Start here |
| -------- | ---------- |
| External site developer | [MERCHANT-GUIDE.md](MERCHANT-GUIDE.md) → [ENDPOINTS-REFERENCE.md](ENDPOINTS-REFERENCE.md) |
| Hub operator / catalog admin | [OPERATOR.md](OPERATOR.md) |
| Protocol details / signing | [PROTOCOL-v1.md](PROTOCOL-v1.md) |
| Machine-readable Hub APIs | [openapi.yaml](openapi.yaml) |
| Architecture overview | [OVERVIEW.md](OVERVIEW.md) |
| Error codes | [ERRORS.md](ERRORS.md) |
| Go-live checklist | [checklists/MERCHANT-GO-LIVE.md](checklists/MERCHANT-GO-LIVE.md) |
| Security checklist | [checklists/SECURITY.md](checklists/SECURITY.md) |
| Samples | [samples/](samples/) |

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
