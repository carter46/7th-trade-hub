# PWA Service Worker – Caching Rules

Use these rules when configuring the Laravel PWA package (or a custom service worker) so dynamic pages are never cached.

## Cache (cache-first or stale-while-revalidate)

- `/build/*` (Vite output)
- `/assets/css/*`
- `/assets/js/*`
- `/assets/images/*`
- `/assets/fonts/*`
- `/icons/*`
- Same-origin requests whose URL path matches the above

## Never cache (network-first or bypass)

- `/login`
- `/register`
- `/forgot-password`
- `/reset-password`
- `/dashboard`
- `/dashboard/*`
- `/admin`
- `/admin/*`
- `/api/*`
- `/messages`
- `/messages/*`
- `/transactions`
- Any request with `Authorization` header or cookie-based session

## Rationale

Caching dynamic or authenticated pages can show:

- Old balances or transaction lists
- Stale user state or admin data

For a financial/trading platform this is unacceptable. The service worker must only cache static assets; all app and API routes must use network-first (or no cache).
