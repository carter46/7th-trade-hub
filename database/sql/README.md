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
2. Export the schema (e.g. from your local MySQL):  
   `php artisan schema:dump`  
   That writes to `database/schema/mysql-schema.sql` (Laravel 11+). Copy or merge the relevant parts into `database/sql/migration.sql`, or replace `migration.sql` with a full export.
3. Commit the updated `database/sql/migration.sql`.
4. On the server, either re-import `migration.sql` into a fresh database or run only the new `CREATE TABLE` / `ALTER TABLE` statements in phpMyAdmin.

## Reference

See `../7th_trade_hub.sql` in the parent folder for a commented reference of all platform tables (auth, app, Spatie, Laravel system).
