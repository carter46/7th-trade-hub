# DomainNameAPI adapter (Phase 2)

Adapter: `App\Services\Domains\Providers\DomainNameApi\DomainNameApiProvider`  
HTTP client: `DomainNameApiClient`

## Authentication

Headers on every request:

- `__reseller` — Reseller ID (numeric/UUID from reseller panel)
- `X-API-KEY` — API key (live or OT&E)

Credentials stored encrypted on `domain_providers` row (`key=domainnameapi`):

- `reseller_id`
- `api_key`

## Endpoints used

| Operation | Method | Path |
|-----------|--------|------|
| TLD list | GET | `/api/v1/products/tlds` |
| Availability | POST | `/api/v1/domains/search` |
| Register | POST | `/api/v1/domains/register-with-contacts` |

Sandbox base URL: `https://ote.domainresellerapi.com`  
Production: `https://api.domainresellerapi.com`

## Fallback routing

Enable DomainNameAPI as a non-default provider with a `fallback_priority`. If the default provider fails on a **new** quote/search, the manager tries fallbacks in priority order.

## Registration fulfillment

After a paid platform order, `DomainRegistrationFulfillmentService` calls `registerDomain()` on the provider recorded on the order line (from quote binding — never switched at fulfillment time).

Default registrant contact and nameservers come from `config/domains.php` (`registration_contacts`, `default_nameservers`).

## Admin

Configure under **Admin → Domain providers → DomainNameAPI**. Customers never see this provider name in checkout or orders.
