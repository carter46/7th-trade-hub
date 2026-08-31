# Name.com adapter (Phase 1)

Adapter: `App\Services\Domains\Providers\NameCom\NameComProvider`  
HTTP client: `NameComClient` (Basic Auth)

## Endpoints

| Operation | Method | Path |
|-----------|--------|------|
| Health | GET | `/core/v1/hello` |
| TLD list | GET | `/core/v1/tldpricing?duration=1` — response rows are in `pricing` (paginated via `lastPage`) |
| Availability | POST | `/core/v1/domains:checkAvailability` |
| Premium pricing | GET | `/core/v1/domains/{fqdn}:getPricing?years=1` |

Sandbox base URL: `https://api.dev.name.com`

## Credentials (admin only)

- Username + API token on `domain_providers` row (`key=namecom`).
- Sandbox toggle per provider row.

## Pricing workflow

1. `checkAvailability` with `purchaseType: registration`.
2. If `premium: true`, call `getPricing` for exact 1-year `purchasePrice`.
3. Reject non-registration `purchaseType` or `purchasable: false`.

## Note on URL encoding

Paths like `/domains/{fqdn}:getPricing` use literal `:` — encode FQDN with `rawurlencode`.

## Registration (fulfillment)

`POST /core/v1/domains` with contacts, `years: 1`, platform `nameservers` from `DOMAIN_NS*`, and `purchasePrice` when premium.  
Send `X-Idempotency-Key` to prevent duplicate registrations on retry.

## Nameserver management

| Operation | Method | Path |
|-----------|--------|------|
| Get domain (incl. NS) | GET | `/core/v1/domains/{domainName}` |
| Set nameservers | POST | `/core/v1/domains/{domainName}:setNameservers` |

Customer changes go through **My Domains** → same Name.com API via `domain_registrations.provider_key`.
