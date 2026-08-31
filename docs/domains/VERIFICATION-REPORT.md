# Production Remediation — Verification Report

Date: 2026-08-31

## Architecture verdict: **READY WITH FIXES**

P0/P1 remediation implemented. Automated test execution **NOT VERIFIED** in agent environment (terminal/PHP unavailable). Run locally before production deploy.

---

## PASS — verified by static inspection

| Item | Evidence |
|------|----------|
| Provider-neutral routing | `DomainProviderManager::quoteThroughTld()`, no provider-key switches in checkout |
| TLD capability vs availability | Primary provider per TLD; domain-unavailable does not cross-provider fallback |
| Gateway quote reserve/consume | `reserveForGateway`, `consumeReservedQuote`, `fulfillPaidGatewayOrder` |
| Decimal pricing policy | `PlatformDomainPricingPolicy` uses `bcmul`/`bcadd`/`bccomp`, ceil NGN |
| Exception taxonomy | `DomainProviderTransportException`, `DomainBusinessException` |
| Name.com getPricing fallback | `NameComProvider::getRegistrationQuote()` non-premium path |
| Disabled provider at checkout | `requireEnabledForCheckout()` |
| Order item privacy | Customer options exclude provider/cost; `domain_quote_id` only |
| Fulfillment lifecycle | `processing`, `registered`, `failed`, `reconciliation_required` |
| Cache invalidation | `DomainCacheInvalidator` on provider + domain product admin update |
| Apex-only FQDN | `DomainFqdn::assertApexOnly()` |
| No common_tlds fallback | `tldOptionsForUi()` returns `[]` on failure |
| HTTP hardening | `SendsDomainProviderRequests` trait, redirects disabled |
| Audit logging | `DomainAuditLogger` wired to quote + fulfillment + provider admin |

---

## NOT VERIFIED — environment limitation

| Item | Command |
|------|---------|
| Domain test suite | `php artisan test tests/Feature/Domains` |
| Site Integration tests | `php artisan test tests/Feature/SiteIntegrations` |
| Catalog trim test | `php artisan test tests/Feature/Catalog/PlatformCatalogTrimTest.php` |
| Full suite | `php artisan test` |
| Browser checkout flows | Manual QA |

---

## Tests added/updated

- `DomainTldRoutingTest` — TLD-only-on-fallback + no cross-provider on unavailable
- `DomainNameComPricingFallbackTest` — getPricing when purchasePrice absent
- `DomainQuoteSecurityTest` — wrong user, wrong FQDN, reuse, disabled provider
- Updated `DomainRegistrationFulfillmentTest` for `domain_quote_id` options

---

## Remaining follow-ups (P2/P3)

- Move domain provider admin routes to `system.manage` (currently `catalog.manage`)
- Full security tamper matrix (gateway abandon, concurrent reserve) — partial coverage
- Admin UI for `reconciliation_required` domain registrations
- `php artisan domains:retry-failed-registrations` command (optional)
- Customer order view: domain fulfillment status badge

---

## Deploy checklist

1. `php artisan migrate`
2. `php artisan test tests/Feature/Domains`
3. Configure domain providers in admin
4. Set `DOMAIN_*` contact/nameserver env vars
5. Commit `public/build/` if frontend changed
