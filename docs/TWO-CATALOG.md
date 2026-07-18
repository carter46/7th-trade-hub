# Two catalogs (Phase 1)

## Architecture

7th Trade Hub keeps **two separate catalogs**:

| Catalog | Owner | Primary tables | Public surfaces |
|---------|-------|----------------|-----------------|
| **Platform products** | Admin | `platform_categories`, `platform_products`, `platform_product_variants`, `platform_product_images` | `/services`, `/templates`, `/website-listings` |
| **Marketplace listings** | Sellers | `categories` (tree), `listings` | `/marketplace` |

Do **not** merge platform SKUs into the `listings` table. Escrow marketplace trades and platform catalog purchases share commerce via unified `orders` + `order_items`.

## Product types (no DB table)

Types live in PHP enum `App\Enums\PlatformProductType` plus labels/icons/routes and **user-facing service groups** in [`config/catalog.php`](../config/catalog.php).

Services browse hierarchy: **Groups** (Network Services, Communication, Social Media, Website Services, Business Documents, Trust & Escrow) → **types** (VPN, Email, …) → **products**. URLs: `/services`, `/services/{group|type}`, `/services/{type}/{slug}`. Marketing copy defaults in config; overrides via `catalog_page_contents` and optional fields on `platform_categories`.

## Visibility

Platform products use `status`: `draft` | `published` | `archived`, plus `is_featured`. Public pages only show `published`.

## Variants (not a prices table)

`platform_product_variants` holds duration plans, tiers, and future region SKUs (`name`/`label`, `duration_months`, `price`, `is_default`).

## JSON content fields (Phase 1)

`features`, `requirements`, `whats_included`, and `faqs` are JSON columns because admins edit them as whole blocks on the product form. If we later need per-item CRUD, ordering UI, localization, or search-inside-features, normalize into relational tables **without changing public routes** (`/services/{type}/{slug}`, etc.).

## Exchange rates

`exchange_rates` drives `/exchange` with buy/sell NGN rates, optional min/max amounts, `processing_time`, and `is_featured`.

## Favorites & reviews

- `favorites` — polymorphic user ↔ listing | platform_product
- `product_reviews` — platform product ratings (marketplace keeps existing `reviews`)

## Seeding (cPanel / production)

After importing `database/sql/migration.sql`:

```bash
php artisan db:seed --class=ProductionSeeder
```

This seeds roles/settings, marketplace + platform category trees, **5–6 products per type** with variants, exchange rates, **10 sample marketplace vendors × 5 listings**, and the platform wallet.

Never run full `db:seed` in production if it pulls demo-only seeders beyond ProductionSeeder.

## Phase 2 backlog (not built)

- Provider integrations (domains, phone, cloud, WHMCS, etc.)
- Template editor + PDF/JPEG export
- Optional normalize of JSON content blocks
- Discovery: recently viewed, related, popular, new arrivals
- Full product-review moderation UI

## Phase 1.1 hardening (shipped)

- Platform purchase credits platform wallet; checkout idempotency; marketplace double-sell lock
- Upgrade DDL / `hasColumn` guards; shared catalog search partial; FAQ admin editor
- Favorites UX + marketing toasts; `ProductionSeeder` always seeds sample marketplace vendors (10×5 listings)
