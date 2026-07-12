# 7th Trade Hub

Laravel + Blade + PWA digital services marketplace (crypto exchange, social growth, document templates, website listings).

## Current state

- **Laravel app is bootstrapped at the project root** (Breeze + Blade installed).
- **prototype-archive/** – Original HTML prototype (source material to migrate into Blade views).
- **docs/** – Architect recommendations, PWA caching rules, deployment notes, API route example, wallets migration example.
- **public/assets/** – Legacy static assets (use `asset('assets/...')` in Blade when needed).
- **public/icons/** – PWA icons live here (192×192, 512×512, maskable).

## Next steps

1. **Run locally**
   - `C:\xampp\php\php.exe artisan serve`
   - `npm run dev` (or `npm run build`)
2. **Configure `.env`**
   - Set `APP_NAME="7th Trade Hub"`
   - Configure DB + mail (SMTP) when ready
3. **Migrate pages**
   - Convert `prototype-archive/*.html` into `resources/views/pages/*.blade.php`
   - Wire routes in `routes/web.php`
4. **Add PWA**
   - Follow [docs/PWA-CACHING-RULES.md](docs/PWA-CACHING-RULES.md)
5. **Deploy (cPanel Git)**
   - See [docs/DEPLOYMENT-CPANEL.md](docs/DEPLOYMENT-CPANEL.md). Build frontend locally (`npm run build`), commit `public/build/`, push; server only pulls. No Node or `artisan migrate` on server. Use `database/sql/migration.sql` in phpMyAdmin for schema.

## Docs

| File | Purpose |
|------|---------|
| [ARCHITECT-RECOMMENDATIONS.md](docs/ARCHITECT-RECOMMENDATIONS.md) | Checklist: assets, middleware, queues, API, wallets, etc. |
| [PWA-CACHING-RULES.md](docs/PWA-CACHING-RULES.md) | What to cache / what not to cache in the service worker. |
| [DEPLOYMENT-CPANEL.md](docs/DEPLOYMENT-CPANEL.md) | VPS/cPanel docroot and Supervisor for queue worker. |
| [API-ROUTES-EXAMPLE.php](docs/API-ROUTES-EXAMPLE.php) | Starter for `routes/api.php`. |
| [migration_wallets_example.php](docs/migration_wallets_example.php) | Wallets table migration to copy into Laravel. |
