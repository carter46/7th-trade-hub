# Deployment on cPanel (Git, no Node / no artisan on server)

This project is deployed on **shared hosting** using **cPanel Git Version Control**. The server runs **PHP only**; it does not run Node.js, npm, or `artisan migrate`.

## Deployment workflow

1. **Local:** Edit code in Cursor (or any editor).
2. **Local:** When frontend assets change (`resources/css` or `resources/js`), run **`npm run build`** so `public/build/` is updated.
3. **Local:** Commit and push to GitHub (e.g. via GitHub Desktop).
4. **Server:** In cPanel → Git Version Control, **Pull** the latest changes from the GitHub repository.
5. **Server (after pull only):** If you have SSH/terminal, run migrations and refresh branding icons — **never run `branding:sync-pwa` before pull** (see [Git pull blocked by icon files](#git-pull-blocked-by-icon-files) below).

Do **not** run `npm run build` on the server (no Node.js). Do **not** run `php artisan test` on shared hosting (`proc_open` is usually disabled).

## Composer / vendor

`vendor/` is tracked in the repository for shared hosting (no Composer on the server). After pull, verify `vendor/autoload.php` exists.

Locally, after changing dependencies: run `composer install --no-dev --optimize-autoloader`, then commit the updated `vendor/` with the lockfile.

## One-time optimization (if terminal available)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Production seeder (once)

```bash
php artisan db:seed --class=ProductionSeeder
```

Requires `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` only if you still create an admin via a separate seeder step. **Never** run full `db:seed` in production (demo dashboard data).

`ProductionSeeder` loads marketplace/platform category trees, platform catalog products (≈6 per type), exchange rates, and **10 sample marketplace vendors** (5 published listings each). Catalog seeders use `firstOrCreate` by slug so re-runs do not overwrite admin edits. Sample vendor password is `password` (delete or change via admin as needed).

After a **schema upgrade** on an existing DB, run new migrations (or the upgrade notes in `database/sql/migration.sql`), then re-run `ProductionSeeder` if needed:

```bash
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
php artisan config:cache
php artisan route:cache
```

See [PRODUCTION-ENV-CHECKLIST.md](PRODUCTION-ENV-CHECKLIST.md) and [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md).

## Repository requirements

- **`public/build/`** is **committed** to the repo (it is **not** in `.gitignore`). The server serves these files as-is.
- **PWA / favicon files** under `public/` (`favicon-*.png`, `favicon.ico`, `apple-touch-icon.png`, `logo.png`, `public/icons/*.png`, `manifest.json`) are **committed** as baselines. After each pull, you may run `php artisan branding:sync-pwa` to regenerate them from admin branding — run it **only after** a successful pull.
- **`.env`** is **not** committed. Configure environment variables on the server manually (copy from `.env.example` and set values in cPanel or via file manager).
- **Database schema** is in **`database/sql/migration.sql`**. When the schema changes, update this file and commit it. Import or re-import it in phpMyAdmin as needed.
- **Legacy `analytics_providers`:** Superseded by `integration_providers`. The SQL still creates the old table for one-time cutover copy only; the app does not write to it. After all environments have migrated, you may `DROP TABLE IF EXISTS analytics_providers;`. Re-run `PermissionSeeder` (or grant `fees.manage`) on existing DBs after fees moved out of Settings.

## One-time server setup

1. **Document root:** Point the domain’s document root to the **`public`** folder of the Laravel app (e.g. `laravel-app/public`).
2. **Database:** In cPanel → MySQL® Databases, create a database and a user with full privileges. Note database name, username, and password.
3. **Import schema:** In phpMyAdmin, select the new database and **Import** `database/sql/migration.sql`.
4. **Environment:** Create `.env` in the Laravel root on the server (copy from `.env.example`). Set at least:
   - `APP_KEY` (generate one locally with `php artisan key:generate --show` and paste, or use cPanel terminal once if available)
   - `APP_ENV=production`, `APP_DEBUG=false`
   - `APP_URL=https://yourdomain.com` (no trailing slash)
   - `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT=3306`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `MAIL_*` for SMTP if you need email (OTP, etc.)
   - `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true` when using HTTPS
   - `QUEUE_CONNECTION=sync` on shared hosting unless a queue worker is configured
   - With `sync`, email delivery still tries Brevo → Brevo retry → Laravel Mail, then notifies admins immediately. Delayed 5/30 minute retries require `database`/`redis` queue + a worker.
5. **Permissions:** Ensure `storage/` and `bootstrap/cache/` are writable by the web server (e.g. 755 or 775 and correct owner). Use cPanel File Manager or FTP.
6. **PWA icons:** Baseline icons are in git under `public/icons/` and `public/favicon-*.png`. After the first successful pull, run `php artisan branding:sync-pwa` once to match admin favicon (see `public/icons/README.md`).

## Git pull blocked by icon files

If cPanel deploy fails with:

```text
error: The following untracked working tree files would be overwritten by merge:
    public/apple-touch-icon.png
    public/favicon-16x16.png
    ...
```

**Cause:** `php artisan branding:sync-pwa` was run **before** `git pull`. That created icon files on the server that are not in git’s index, then pull tried to write the committed versions from GitHub.

**Fix (SSH or cPanel Terminal — run from the Laravel app root, same folder as `artisan`):**

```bash
cd ~/domains/7th-tradehub.online/public_html   # adjust if your path differs

# Remove only the conflicting untracked icons (safe — pull restores them from git)
rm -f public/apple-touch-icon.png public/favicon-16x16.png public/favicon-32x32.png public/favicon.ico public/logo.png

git pull origin main    # or master — match your default branch

php artisan migrate --force
php artisan branding:sync-pwa
php artisan config:cache
php artisan route:cache
```

**Correct order every deploy:**

1. **Pull** (cPanel Git or `git pull`)
2. **`php artisan migrate --force`** (if you use migrations on server)
3. **`php artisan branding:sync-pwa`** (optional — refreshes favicon/PWA from admin settings)
4. **Cache** (`config:cache`, `route:cache`) if you use them

Never run step 3 before step 1.

## Local workflow summary

| When | Do this |
|------|--------|
| You change PHP/Blade/config | Commit and push. Server pulls. |
| You change `resources/css` or `resources/js` | Run **`npm run build`**, then commit (including `public/build/`) and push. |
| You add or change DB tables (migrations) | Run migrations locally, update **`database/sql/migration.sql`** (e.g. from `php artisan schema:dump` or by hand), commit and push. On the server, import the new schema in phpMyAdmin or run the new statements. |

## Queue worker (optional)

If the app uses queues and your host allows long-running processes (e.g. cron or a worker), configure a cron job to run `php /path/to/artisan queue:work` or use the host’s “worker” feature if available. Many shared hosts do not support this; the app will still run without it if queues are not critical.

## Scheduled tasks (cron)

If cPanel cron or SSH is available, add a single entry so Laravel’s scheduler runs (required for analytics rollups, GA sync, activity pruning, and monitoring heartbeats):

```bash
* * * * * cd /path/to/laravel-app && php artisan schedule:run >> /dev/null 2>&1
```

Registered commands (see `routes/console.php`):

| Command | Schedule |
|---------|----------|
| `analytics:rollup-kpis` | Hourly |
| `analytics:prune-activity` | Daily 04:00 |
| `analytics:sync-ga` | Daily 05:00 |
| `monitoring:heartbeat` | Every 5 minutes |

After changing `resources/js/app.js`, run **`npm run build`** locally and commit `public/build/` before pulling on the server (command palette entity search depends on the built bundle; an inline fallback exists for admin search until rebuild).
