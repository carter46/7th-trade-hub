# Deployment on cPanel (Git, no Node / no artisan on server)

This project is deployed on **shared hosting** using **cPanel Git Version Control**. The server runs **PHP only**; it does not run Node.js, npm, or `artisan migrate`.

## Deployment workflow

1. **Local:** Edit code in Cursor (or any editor).
2. **Local:** When frontend assets change (`resources/css` or `resources/js`), run **`npm run build`** so `public/build/` is updated.
3. **Local:** Commit and push to GitHub (e.g. via GitHub Desktop).
4. **Server:** In cPanel → Git Version Control, **Pull** the latest changes from the GitHub repository.
5. **Server:** No `npm install`, no `npm run build`, no `php artisan migrate`. The repo already contains built assets and the database is managed via phpMyAdmin.

## Composer / vendor (required once)

The server needs `vendor/` to boot Laravel.

- **If cPanel terminal or SSH is available:** run once in the project root:
  ```bash
  composer install --no-dev --optimize-autoloader
  ```
- **If no terminal:** run `composer install --no-dev` locally and commit the `vendor/` directory (remove `/vendor` from `.gitignore` only for this deployment path).

Verify `vendor/autoload.php` exists after deploy.

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

Requires `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env`. **Never** run full `db:seed` in production (demo data).

See [PRODUCTION-ENV-CHECKLIST.md](PRODUCTION-ENV-CHECKLIST.md) and [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md).

## Repository requirements

- **`public/build/`** is **committed** to the repo (it is **not** in `.gitignore`). The server serves these files as-is.
- **`.env`** is **not** committed. Configure environment variables on the server manually (copy from `.env.example` and set values in cPanel or via file manager).
- **Database schema** is in **`database/sql/migration.sql`**. When the schema changes, update this file and commit it. Import or re-import it in phpMyAdmin as needed.

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
5. **Permissions:** Ensure `storage/` and `bootstrap/cache/` are writable by the web server (e.g. 755 or 775 and correct owner). Use cPanel File Manager or FTP.
6. **PWA (optional):** Add `public/icons/icon-192x192.png` and `public/icons/icon-512x512.png` so the PWA install icon is correct (see `public/icons/README.md`).

## Local workflow summary

| When | Do this |
|------|--------|
| You change PHP/Blade/config | Commit and push. Server pulls. |
| You change `resources/css` or `resources/js` | Run **`npm run build`**, then commit (including `public/build/`) and push. |
| You add or change DB tables (migrations) | Run migrations locally, update **`database/sql/migration.sql`** (e.g. from `php artisan schema:dump` or by hand), commit and push. On the server, import the new schema in phpMyAdmin or run the new statements. |

## Queue worker (optional)

If the app uses queues and your host allows long-running processes (e.g. cron or a worker), configure a cron job to run `php /path/to/artisan queue:work` or use the host’s “worker” feature if available. Many shared hosts do not support this; the app will still run without it if queues are not critical.
