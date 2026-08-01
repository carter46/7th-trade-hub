# PWA / branding icons

Derived assets (do not edit by hand). Regenerated from **Admin → Settings → Site branding**
(favicon preferred, otherwise light/dark logo):

- `icon-192x192.png`
- `icon-512x512.png`

Also written to `public/`:

- `apple-touch-icon.png`
- `favicon-16x16.png` / `favicon-32x32.png` / `favicon.ico`
- `logo.png` (alias for legacy PWA package)

Deploy command:

```bash
php artisan branding:sync-pwa
```

Ensure `public/` and `public/icons/` are writable and PHP GD is enabled.
