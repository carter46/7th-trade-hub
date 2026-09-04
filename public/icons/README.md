# PWA / branding icons

Derived assets under `public/` and `public/icons/` are regenerated from
**Admin → Settings → Site branding** (favicon preferred, otherwise light/dark logo).

## Important

- Browser tabs and social previews **prefer the live media library URL** when a
  favicon/logo is set. That avoids a flash of the letter-7 icon after `git pull`
  restores committed fallback files under `public/`.
- `php artisan branding:sync-pwa` rewrites `public/` icons from branding media.
  If media is configured but unreadable, sync **fails** and does **not** overwrite
  with letter-7.
- If **no** branding media is set, sync **preserves** existing public icons
  (does not paint letter-7 over a good logo).

Deploy (after `git pull` only):

```bash
php artisan branding:sync-pwa
```

Then confirm Admin → Settings still has Favicon / Logo selected, and open:

- the site tab icon
- `https://your-domain/icons/og-image.png` (after a successful media sync)

WhatsApp/Facebook cache OG images — re-scrape after fixing icons.
