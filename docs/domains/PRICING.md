# Domain pricing

Retail NGN is computed server-side only:

```
ngn_cost = provider_cost × usd_ngn_rate   (USD providers)
retail   = ceil(ngn_cost × (1 + markup%/100))
```

## Decimal safety

`PlatformDomainPricingPolicy` uses `bcmul` / `bcadd` / `bccomp` (scale 4 FX, 2 retail display).

## Rounding

Retail is **ceiled to whole NGN** (conservative; never undercuts provider cost after FX).

## Configuration

On domain product meta:

- `domain_markup_percent` (≥ 0; invalid values throw at quote time)
- `domain_fx_policy.usd_ngn_rate` (required for USD providers)

Publishing a domain product is blocked if markup/FX would price below provider floor.

## Browse vs checkout

TLD list prices are for “From ₦X” display only. Checkout authority is always per-domain quote from the bound provider.
