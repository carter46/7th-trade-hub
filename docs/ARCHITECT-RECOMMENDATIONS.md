# Architect Recommendations – Implementation Checklist

Applied from the technical review. Use this when bootstrapping Laravel and when adding PWA, queues, and admin routes.

---

## 1. Prototype location

- **Done:** Renamed `prototype/` to `prototype-archive/`.
- **Rule:** Do not use files from `prototype-archive/` in the live app; copy markup into Blade views only. This avoids accidentally reusing old HTML.

---

## 2. Asset management

- **After Laravel is installed**, create under `public/`:
  - `public/assets/css`
  - `public/assets/js`
  - `public/assets/images`
  - `public/assets/fonts`
- In Blade, always use the helper:
  - `{{ asset('assets/css/style.css') }}`
  - `{{ asset('assets/js/app.js') }}`
  - `{{ asset('assets/images/logo.png') }}`
- If using Vite for built assets, they go in `public/build/` and you use `@vite()` in layouts; use `public/assets/` only for non-Vite static files (e.g. from the prototype).

---

## 3. Middleware planning

- **User dashboard:** protect with `auth` and `verified`.
- **Admin:** protect with `auth` and `admin` (or a role middleware like `role:admin`).

**Example in `routes/web.php`:**

```php
// Guest / public
Route::get('/', ...);

// User dashboard (must be logged in + verified email)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', ...);
    Route::get('/dashboard/exchange', ...);
    Route::get('/dashboard/messages', ...);
    // ...
});

// Admin (must be logged in + admin role)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', ...);
    Route::get('/users', ...);
    Route::get('/transactions', ...);
    // ...
});
```

- Create an `admin` middleware that checks the user’s role (e.g. `role === 'admin'` or use **Spatie Laravel Permission** later).

---

## 4. PWA caching strategy (critical)

- **Cache only (e.g. cache-first):**
  - `/css`, `/js`, `/images`, `/fonts`, `/icons`, `/build`
- **Never cache (network-first or bypass):**
  - `/login`, `/register`, `/dashboard`, `/admin`, `/api`, `/messages`, `/transactions`, and any other dynamic or authenticated page.
- For a financial/trading platform, caching dynamic pages can show stale balances or transactions. In the service worker, cache only static asset URLs; for HTML/navigation to app areas, use **network-first** (or no cache). See `docs/PWA-CACHING-RULES.md` for details.

---

## 5. Replace PHPMailer

- **Rule:** Do not use the PHPMailer code in the app. Use **Laravel Mail (Symfony Mailer)** only.
- PHPMailer remains only inside `prototype-archive/` for reference. Configure mail in `.env` and `config/mail.php`; use Mailables and Laravel notifications for verification, password reset, and admin alerts.

---

## 6. Queues

- In `.env` set:
  - `QUEUE_CONNECTION=database`
- Run migrations so the `jobs` table exists, then run:
  - `php artisan queue:work`
- Use queues for: verification emails, password reset emails, notifications, order updates, admin alerts. Do not send these synchronously in the request.
- **On VPS:** run the worker under **Supervisor** so it restarts automatically. See `docs/DEPLOYMENT-CPANEL.md`.

---

## 7. API routes

- Use `routes/api.php` from the start. Add placeholder or real endpoints for:
  - `/api/transactions`
  - `/api/messages`
  - `/api/notifications`
- This keeps API and web concerns separated and prepares for mobile apps or a future React dashboard. See `docs/API-ROUTES-EXAMPLE.php` for a starter snippet.

---

## 8. PWA icons

- Put icons under `public/icons/` (or where your PWA package expects):
  - **192×192** (required)
  - **512×512** (required)
  - **Maskable** variant (recommended for Android)
- Reference them in `manifest.webmanifest`. After Laravel is installed, create `public/icons/` and add these files.

---

## 9. Deployment (cPanel)

- Project lives in e.g. `home/laravel-app/` (or your chosen path).
- **Domain document root must point to:** `laravel-app/public` (not the repo root).
- Set permissions on `storage/` and `bootstrap/cache/` as per Laravel docs. See `docs/DEPLOYMENT-CPANEL.md`.

---

## 10. Database – wallets table

- Add a **wallets** table early (crypto/balance use case). Example columns:
  - `id`, `user_id`, `currency`, `balance`, `locked_balance`, `created_at`, `updated_at`
- Use a Laravel migration; see `docs/migration_wallets_example.php` for a copy-paste migration stub.

---

## Quick reference

| # | Item              | Status / action |
|---|-------------------|------------------|
| 1 | prototype-archive | Done (folder renamed) |
| 2 | public/assets/*   | Create after Laravel install; use `asset()` in Blade |
| 3 | Middleware        | auth + verified (dashboard), auth + admin (admin) |
| 4 | PWA cache         | Static only; network-first for app/api pages |
| 5 | PHPMailer         | Do not use; Laravel Mail only |
| 6 | Queues            | QUEUE_CONNECTION=database; queue:work; Supervisor on VPS |
| 7 | API routes        | Use routes/api.php; see API-ROUTES-EXAMPLE.php |
| 8 | PWA icons         | 192, 512, maskable in public/icons/ |
| 9 | Deployment        | Docroot = laravel-app/public |
| 10| Wallets           | Add migration; see migration_wallets_example.php |
