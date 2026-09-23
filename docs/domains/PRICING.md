# Domain pricing

## Provider mode

Retail NGN is computed server-side only:

```
ngn_cost = provider_cost × usd_ngn_rate   (USD providers)
retail   = ceil(ngn_cost × (1 + markup%/100))
```

### Decimal safety

`PlatformDomainPricingPolicy` uses `bcmul` / `bcadd` / `bccomp` (scale 4 FX, 2 retail display).

### Rounding

Retail is **ceiled to whole NGN** (conservative; never undercuts provider cost after FX).

### Configuration

On domain product meta:

- `domain_markup_percent` (≥ 0; invalid values throw at quote time)
- `domain_fx_policy.usd_ngn_rate` (required for USD providers)
- `allowed_tlds` (extension allowlist)

Publishing a domain product is blocked if markup/FX would price below provider floor.

## Manual mode (all providers disabled)

Admin sets a fixed NGN price per allowed extension on the Domain product Allowed extensions UI (`domain_manual_tld_prices`).

- Customer quote locks that retail on `domain_quotes` (token-bound).
- Checkout never trusts a browser-submitted price.
- Later admin price edits do not change already-issued quotes / paid order lines.
- Manual price fields are **hidden** in admin when any provider is enabled.

## Browse vs checkout

TLD list prices are for display. Checkout authority is always the per-domain quote (provider-bound or manual-locked).
