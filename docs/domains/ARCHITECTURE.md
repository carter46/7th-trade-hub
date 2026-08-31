# Domain services architecture

7th Trade Hub treats domain registration as a **provider-neutral** capability. Resellers (Name.com, DomainNameAPI, future adapters) plug in behind `DomainProviderInterface` only.

## Components

| Layer | Responsibility |
|-------|----------------|
| `DomainProviderInterface` | Normalized search, quote, TLD list, register |
| `DomainProviderManager` | TLD registry, primary provider per TLD, transport-only fallback |
| `DomainQuoteService` | Issue / reserve / consume quote tokens |
| `PlatformDomainPricingPolicy` | Markup + FX → retail NGN (decimal-safe, ceil whole NGN) |
| `DomainCheckoutValidator` | Website buy/connect + standalone domain rules |
| `PlatformCheckoutService` | Order lines with server-side quoted prices |

## TLD capability vs domain availability

| Concept | Meaning |
|---------|---------|
| **TLD capability** | Which provider(s) sell a TLD (`listTlds` → merged registry with `primaryProviderKey`) |
| **Domain availability** | Whether a specific FQDN is registrable **now** via the **selected** provider |

Rules:

1. Quote routing uses the **primary provider for the TLD** (default if it supports the TLD, else the advertiser).
2. **Domain unavailable** from that provider is final — no silent switch to another provider that also supports the TLD.
3. **Transport/API failures** may try other TLD-capable providers in fallback order.
4. After quote issue, `provider_key` is bound through consume and fulfillment.

## Quote lifecycle

`issued` → (`reserved` for gateway pending orders) → `consumed`

- **Wallet:** consume in same DB transaction as payment.
- **Gateway:** preview at pending order, **reserve** with `reserved_order_id`, **consume** on payment confirmation.

## Customer rule

Users see: Search → Available/Unavailable → Price → Checkout. No registrar names or wholesale costs in product UI, checkout JSON, or order views.

## Fulfillment

`domain_registrations` stores provider metadata internally. Order item options contain only customer-safe fields (`domain_fqdn`, `domain_mode`, `premium`, `domain_quote_id`).

Statuses: `processing`, `registered`, `failed`, `reconciliation_required`.

## Adding a provider

1. Implement `DomainProviderInterface`.
2. Register adapter in `config/domains.php` and seed `domain_providers`.
3. No changes to checkout, quote service, or customer UI.

## Related docs

- [PROVIDER-CONTRACT.md](PROVIDER-CONTRACT.md)
- [PRICING.md](PRICING.md)
- [QUOTES.md](QUOTES.md)
- [CHECKOUT.md](CHECKOUT.md)
- [SECURITY.md](SECURITY.md)
- [FULFILLMENT.md](FULFILLMENT.md)
- [VERIFICATION-REPORT.md](VERIFICATION-REPORT.md)
