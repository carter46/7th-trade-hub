# Domain registration fulfillment

Runs on `OrderCompleted` for paid platform orders with domain purchase lines (`domain_mode=buy` + quote id).

## Modes

| Quote / line | Behavior |
|--------------|----------|
| Provider | When `DOMAIN_AUTO_REGISTER=true`: availability + cost drift + `registerDomain` |
| Manual (`provider_key=manual` / `domain_fulfillment=manual`) | Create `pending_manual` registration; **no** provider availability, pricing, balance, or register calls; no auto-retry as provider |

## State machine

```
processing → registered
processing → failed
processing → reconciliation_required
pending_manual → registered (admin marks done offline)
```

## Idempotency

One `domain_registrations` row per order item. Skips if already present.

## Provider binding

Fulfillment uses `domain_quotes` linked via `order_items.options.domain_quote_id` — never switches provider. Manual rows stay manual even if providers are later re-enabled.

## Cost ceiling (provider only)

Before `registerDomain()`, fresh provider cost is compared to `provider_cost_at_checkout`. Increase beyond drift tolerance → `reconciliation_required` (no silent overspend).

## Paid-but-failed

Registration failure after payment does **not** auto-refund (provider may have registered on timeout). Ops reviews `reconciliation_required` / `failed` / `pending_manual` rows.

Set `DOMAIN_AUTO_REGISTER=false` to disable API registration for provider-mode orders (manual pending rows are still created).

## Contacts / nameservers

Registrant contact is collected at checkout per customer. See [NAMESERVERS.md](NAMESERVERS.md) for nameserver defaults, per-domain snapshots, and My Domains management.
