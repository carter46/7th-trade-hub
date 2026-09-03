# PWA / branding icons

Derived assets (do not edit by hand). Regenerated from **Admin → Settings → Site branding**
(favicon preferred, otherwise light/dark logo):

- `icon-192x192.png` / `icon-512x512.png` — tight `purpose: any` (logo fills the canvas)
- `icon-192x192-maskable.png` / `icon-512x512-maskable.png` — white background + ~80% safe zone
- `og-image.png` — social preview (1200×630), not the letter-7 tile

Also written to `public/`:

- `apple-touch-icon.png`
- `favicon-16x16.png` / `favicon-32x32.png` / `favicon.ico` (multi-size PNG-in-ICO)
- `logo.png` (alias for legacy PWA package)

Deploy command (after `git pull` only):

```bash
php artisan branding:sync-pwa
```

Ensure `public/` and `public/icons/` are writable and PHP GD is enabled.
