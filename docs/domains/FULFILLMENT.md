# Domain registration fulfillment

Runs on `OrderCompleted` for paid platform orders with domain purchase lines.

## State machine

```
processing → registered
processing → failed
processing → reconciliation_required
```

## Idempotency

One `domain_registrations` row per order item. Skips if already registered.

## Provider binding

Fulfillment uses `domain_quotes` linked via `order_items.options.domain_quote_id` — never switches provider.

## Cost ceiling

Before `registerDomain()`, fresh provider cost is compared to `provider_cost_at_checkout`. Increase beyond drift tolerance → `reconciliation_required` (no silent overspend).

## Paid-but-failed

Registration failure after payment does **not** auto-refund (provider may have registered on timeout). Ops reviews `reconciliation_required` / `failed` rows.

Set `DOMAIN_AUTO_REGISTER=false` to disable API registration.

## Contacts / nameservers

Configure via `DOMAIN_CONTACT_*` and `DOMAIN_NS*` env vars (see `config/domains.php`).
