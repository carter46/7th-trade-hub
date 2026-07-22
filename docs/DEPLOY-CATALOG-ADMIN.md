# Catalog hierarchy & admin deploy notes

## Hostinger / shared hosting

1. Pull latest code (includes tracked `public/build` if assets changed).
2. Run migrations:
   ```bash
   php artisan migrate --force
   ```
3. Backfill hierarchy (idempotent; required on existing DBs):
   ```bash
   php artisan catalog:backfill-hierarchy
   ```
4. Clear caches:
   ```bash
   php artisan view:clear
   php artisan config:clear
   php artisan route:clear
   ```
5. Optional: set `CATALOG_USE_DB_HIERARCHY=true` in `.env` (default true when config key is present).

## What changed

- Platform catalog: `service_categories` → `product_types` (admin label **Services**) → `platform_products` (+ provider fields, variants).
- Legacy `platform_categories` removed after cleanup migration.
- Admin nav: Operations / Platform Catalog / Marketplace / Crypto Exchange / Finance / System.
- User admin: create, password reset link, verify email, wallet provision, impersonation.

## Rollback note

Phase 2.5 dual-read kept string `product_type` temporarily; cleanup migration drops `platform_categories`. Restore from DB backup if you must roll back past that migration.
