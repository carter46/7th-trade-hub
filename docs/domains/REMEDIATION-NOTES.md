# Production Remediation — Code vs Audit Mapping

Generated during Phase 0 inspection. Code is authoritative where noted.

| Item | Audit claim | Actual code (verified) | Planned delta |
|------|-------------|------------------------|---------------|
| P0-1 | Merged TLDs lack provider binding | `DomainTld` has only `tld`, cost, currency | Add `primaryProviderKey`, `supportedProviderKeys`; TLD-aware quote routing |
| P0-1 | `quoteForUser` uses blind `attempt()` | Confirmed — default-first, no TLD routing | `quoteForTld()` with primary + transport-only fallback |
| P0-2 | Gateway consumes quote at pending order | Confirmed — `buildCheckout` → `consumeForPurchase` for all methods | `reserveForGateway` + consume on `fulfillPaidGatewayOrder` |
| P1-2 | `attempt()` catches all `Throwable` | Confirmed line 32 | Catch only retryable exceptions |
| P1-1 | Float pricing | Confirmed in `PlatformDomainPricingPolicy` | `bcmul`/`bcadd`/`bccomp` |
| P2-5 | `common_tlds` fallback | Confirmed in `tldOptionsForUi` lines 203–206 | Return `[]`, fail closed |
| P1-8 | Provider in `order_items.options` | Confirmed in `domainLineFromQuote` | Customer-safe options only; fulfillment via `domain_registrations` |
