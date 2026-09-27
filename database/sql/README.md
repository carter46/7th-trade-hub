# Database SQL for phpMyAdmin (cPanel)

This folder holds the schema file for creating a **brand-new, empty** database in phpMyAdmin.

**Existing databases are upgraded with migrations, not with this file:** take a verified backup, pull, then run `php artisan migrate --force`. Migrations contain safety guards and data cleanup that the SQL below does not.

## migration.sql

- **Purpose:** Full current schema for import via phpMyAdmin.
- **When to use:** On shared hosting where you manage the database through cPanel only.
- **How to use:**
  1. In cPanel, create a MySQL database and user.
  2. Open phpMyAdmin, select that database.
  3. Import `migration.sql` (Import tab → Choose file → Go).
  4. Set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env` on the server to match.

## When the schema changes

When you add or change tables (new migrations locally):

1. Run `php artisan migrate` locally so your local DB is up to date.
2. Update `database/sql/migration.sql` so fresh imports match.
3. Commit the updated SQL file.
4. On the server, back up the database, then run `php artisan migrate --force` after pulling.

**Default roles** are seeded at the end of `migration.sql` (`admin`, `user`). Registration requires these rows.

**Production admin:** use `php artisan db:seed --class=ProductionSeeder` or `seed_production_admin.sql.example` in phpMyAdmin.

**Two-catalog Phase 1** tables (`platform_*`, `order_items`, `favorites`, `product_reviews`) and enriched `orders` columns are included in `migration.sql`.

**Removed features (2026-09-27):** the crypto exchange (`crypto_*`, `exchange_rates`, `exchange_rate_history`, `otc_pricing_settings`, `incoming_crypto_transactions`, `wallet_balance_history`), escrow and the peer marketplace (`escrows`, `listings`, `listing_versions`, `categories`, `marketplace_products`, `messages`, `reviews`, `watchlists`) are no longer in `migration.sql`. Existing databases drop them via `php artisan migrate --force` after a verified backup — see the note at the end of `migration.sql`. The migrations drop tables only: user rows are never changed, and existing `orders.listing_id` / `transactions.escrow_id` columns keep their values (only their foreign keys go). Fresh imports simply don't have those two unused columns. Catalog seed data comes from `ProductionSeeder` — see `docs/TWO-CATALOG.md`.

## Reference

See `../7th_trade_hub.sql` in the parent folder for a commented reference of all platform tables (auth, app, Spatie, Laravel system).
