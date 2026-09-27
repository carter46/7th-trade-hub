# Platform catalog

7th Trade Hub has **one catalog**, owned by the admin:

| Catalog | Owner | Primary tables | Public surfaces |
|---------|-------|----------------|-----------------|
| **Platform catalog** | Admin | `service_categories`, `product_types` (UI: Services), `platform_products`, `platform_product_variants`, `platform_product_images` | `/services`, `/templates`, `/website-listings` |

The second catalog (seller-owned marketplace listings in `categories` / `listings`, with escrow) was removed on 2026-09-27 together with the crypto exchange. Old `/marketplace` URLs 301-redirect to `/services`. Purchases go through `orders` + `order_items` with `source = 'platform'`.

## Glossary (locked)

| Term | Means | Does not mean |
|------|-------|---------------|
| **Service Category** | Top business division (`service_categories`) | Legacy `platform_categories` |
| **Service** | Mid-level offer line under a category (`product_types` table; admin label **Services**) | A sellable SKU; a billing plan |
| **Product** | Sellable SKU under a Service (`platform_products`) | A duration/RAM tier |
| **Variant** | Plan/tier under a Product (`platform_product_variants`) | A Service |
| **Provider** | Fulfillment backend fields on a product (Namecheap, Twilio, …) | A Service Category |

Hierarchy:

```
Service Category (e.g. Network Services)
  └── Service (e.g. VPN)
        └── Product (e.g. NordVPN Premium)
              ├── Variant (1 Month)
              ├── Variant (12 Months)
              └── Variant (24 Months)
```

There is **no fourth layer**. Legacy `platform_categories` (Residential, Gaming, …) are migrated into Services or Products and then removed.

## Source of truth

- Public browse, admin CRUD, and search read **`service_categories` → `product_types` → `platform_products`** from the database.
- `config/catalog.php` may remain as **seed defaults only**, not a live CMS.
- `PlatformProductType` enum is **not** the source of truth after cutover (temporary redirect maps during dual-read only).

## Visibility

Platform products use `status`: `draft` | `published` | `archived`, plus `is_featured`. Public pages only show `published`.

## Variants

`platform_product_variants` holds duration plans, tiers, and future region SKUs. Published products should have at least one active variant. Do not model plans as Services.

## Provider fields (schema ready; integrations later)

On `platform_products`: `provider`, `provider_product_id`, `provider_sku`, `provider_meta`, `fulfillment_mode` (`manual` | `auto_provision`), `auto_renew`.

## Trust & Escrow (removed)

The `trust-escrow` category (`mode = marketplace_link`) was retired on 2026-09-27. Existing databases keep the row (the removal migrations only drop tables), but its key is no longer in `config/platform_categories.php`, so `ServiceCategory::system()` hides it everywhere. `/services/trust-escrow` now 301-redirects to `/services`.

## Migration mapping

See [CATALOG-MIGRATION-MAP.md](./CATALOG-MIGRATION-MAP.md).

## Seeding

```bash
php artisan db:seed --class=ProductionSeeder
php artisan catalog:backfill-hierarchy   # idempotent dual-read backfill
```

## Phase 2 backlog (integrations)

- Live provider adapters (Namecheap, Twilio, WHMCS, …)
- Template editor + PDF/JPEG export
- Optional normalize of JSON content blocks
- Discovery: recently viewed, related, popular
