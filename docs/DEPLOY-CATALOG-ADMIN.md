# Catalog hierarchy & admin deploy notes

## Hostinger / shared hosting

1. Pull latest code (includes tracked `public/build` if assets changed).
2. **Preflight (required before migrate on production):** confirm live `service_categories` still match the six fixed id↔slug pairs:
   - `1` → `network-services`
   - `2` → `communication`
   - `3` → `social-media`
   - `4` → `website-services`
   - `5` → `business-documents`
   - `6` → `trust-escrow`  
   If anything differs, **STOP** — do not run the key migration.
3. Run migrations (adds `service_categories.key` and backfills only; aborts on mismatch):
   ```bash
   php artisan migrate --force
   ```
4. Optional hierarchy repair (idempotent; **non-destructive** for existing category/service CMS names and images — keys only when missing):
   ```bash
   php artisan catalog:backfill-hierarchy
   ```
5. Clear caches:
   ```bash
   php artisan view:clear
   php artisan config:clear
   php artisan route:clear
   ```
6. Optional: set `CATALOG_USE_DB_HIERARCHY=true` in `.env` (default true when config key is present).

## Fixed platform catalog

- Categories / services / products are **integral** — admin cannot Add or Delete.
- Categories & services: edit public **name** + activate/deactivate; slugs frozen; permanent category `key` in code (`config/platform_categories.php`).
- Products: edit title, short/long description, base price, hero image, status (published↔draft), and **existing variant prices only**.
- Public + user browse hide products unless category **and** service are active and product is published.
- Marketplace and Crypto/OTC are unchanged.

**Do not run `php artisan db:seed` or `ProductionSeeder` on Hostinger production.** Seeders are for fresh/local installs. Variant seeding is non-destructive (`firstOrCreate`), but production content should only change via admin + migrate/backfill.

## What changed (history)

- Platform catalog: `service_categories` → `product_types` (admin label **Services**) → `platform_products` (+ variants).
- Legacy `platform_categories` removed after cleanup migration.
- Admin nav: Operations / Platform Catalog / Marketplace / Crypto Exchange / Finance / System.

## Rollback note

Phase 2.5 dual-read kept string `product_type` temporarily; cleanup migration drops `platform_categories`. Restore from DB backup if you must roll back past that migration.
