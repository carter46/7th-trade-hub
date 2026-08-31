# Domain quotes

Quotes bind: user, product, FQDN, provider, retail price, expiry.

## Token security

- 64-char random plain token (browser); SHA-256 hash stored server-side.
- Never expose provider credentials or wholesale cost in JSON.

## Lifecycle

| State | When |
|-------|------|
| Issued | After successful `quoteForUser` |
| Reserved | Gateway pending order (`reserved_at`, `reserved_order_id`) |
| Consumed | Wallet checkout or gateway payment confirmed (`consumed_at`) |

Gateway abandoned checkout: reservation can be released; quote not consumed until payment.

## Consumption rules

- Re-validate availability + price drift with **same provider** at consume.
- Reject wrong user, FQDN, product, expired, or already consumed tokens.
- Disabled provider blocks new reserve/consume (fail closed).

## Drift

Configurable tolerance (`DOMAIN_PRICE_DRIFT_TOLERANCE_PERCENT`, default 2%). Exceeding tolerance → customer must search again.
