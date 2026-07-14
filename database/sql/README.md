# Database SQL for phpMyAdmin (cPanel)

This folder holds the schema file for deployment when the server **does not run** `php artisan migrate`.

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
2. Update `database/sql/migration.sql` to match (or run new statements from migrations in phpMyAdmin on existing DBs).
3. Commit the updated SQL file.
4. On the server, re-import on a fresh database, or run only the new `CREATE` / `ALTER` statements.

**Default roles** are seeded at the end of `migration.sql` (`admin`, `user`). Registration requires these rows.

**Production admin:** use `php artisan db:seed --class=ProductionSeeder` or `seed_production_admin.sql.example` in phpMyAdmin.

## Reference

See `../7th_trade_hub.sql` in the parent folder for a commented reference of all platform tables (auth, app, Spatie, Laravel system).
