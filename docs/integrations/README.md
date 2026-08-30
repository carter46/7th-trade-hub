# Site Integration Platform — Documentation

7th Trade Hub acts like a **payment gateway**: Hub owns APIs, signing, admin tooling, and this documentation. Independent websites (merchants) implement endpoints and config described here. Hub never needs your application source code.

## Who should read what

| Audience | Start here |
| -------- | ---------- |
| External site developer | [MERCHANT-GUIDE.md](MERCHANT-GUIDE.md) |
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

Current: **v1** (`protocol: 7th-tradehub`). See [CHANGELOG.md](CHANGELOG.md).
