# Deploying Media Library

After pulling Media Library changes, run these on each environment.

## 1. Migrate

```bash
php artisan migrate --force
```

Creates/updates `media_assets`, `media_variants`, `media_usages`, catalog `*_media_id` columns, gallery `media_asset_id`, and optional `tags` / `collection` / `brand_key`.

## 2. Public storage link

```bash
php artisan storage:link
php artisan config:clear
```

Confirm URLs like `https://yourdomain.com/storage/media/...` resolve (root `/storage/...`, **not** under `/admin/...`).

**APP_URL must include the scheme**, e.g. `APP_URL=https://7th-tradehub.online`.  
If set to `7th-tradehub.online` without `https://`, image URLs become relative and break on nested admin pages.

Public disk URLs default to root-relative `/storage` (`FILESYSTEM_PUBLIC_URL`).

## 3. Frontend assets

Hostinger has no npm — **commit and deploy `public/build`**.

Locally / in CI:

```bash
npm ci
npm run build
```

## 4. Optional config

Review `.env` / `config/media.php`:

- `MEDIA_DISK` (default `public` — keep until CDN cutover)
- `MEDIA_MAX_UPLOAD_KB`
- `MEDIA_DOCUMENTS_DISK` (private proofs)
- `MEDIA_BRAND_KEY` / `MEDIA_DEFAULT_COLLECTION` (white-label / collections)
- Do **not** enable `document` in `allowed_types` without verifying deposit proof download

## 5. Maintenance

```bash
# Dry-run soft-deleted asset GC
php artisan media:purge-soft-deleted --days=30

# Actually purge files + rows
php artisan media:purge-soft-deleted --days=30 --force
```

## Smoke check

1. Admin → **Media Library** → upload an image.
2. Service Categories / Services → set Image → save → public page shows that image as card and header.
3. Product form → pick hero → public product page shows the image.
4. Media Library → Replace on an in-use asset → public page updates.
5. Users tabs switch without browser console errors / growing scroll lag.
6. Bank deposit with proof → admin can download proof.

## Rollback notes

- `migrate:rollback` does not delete files under `storage/app/public/media`.
- Prefer `media:purge-soft-deleted` after soft deletes; destroy now force-deletes unused assets and purges files.
