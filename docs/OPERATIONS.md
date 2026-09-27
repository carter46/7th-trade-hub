# Operations

Production operations guide for 7th Trade Hub on cPanel or VPS.

## Uptime monitoring

- **Health endpoint:** `GET /up` (Laravel built-in). Expect HTTP 200.
- Configure an external monitor (UptimeRobot, Better Stack, etc.) to ping every 5 minutes.
- Alert on non-200 or response time > 10s.

## Logging

| Environment | Recommended settings |
|-------------|-------------------|
| Production | `LOG_LEVEL=warning`, `LOG_STACK=daily`, `LOG_DAILY_DAYS=14` |
| Local | `LOG_LEVEL=debug`, `LOG_STACK=single` |

- Application logs: `storage/logs/`
- Financial audit trail: `audit_logs` table (never delete)
- Ledger: `transactions` table (append-only; use `reverseTransaction()` for corrections)

### Optional: Sentry

```bash
composer require sentry/sentry-laravel
```

Set in `.env`:

```
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
```

The app auto-registers Sentry when the package is installed.

### Optional: Slack alerts

Set `LOG_SLACK_WEBHOOK_URL` and add `slack` to `LOG_STACK` for critical errors.

## Cron / scheduler

Add this cron entry (cPanel → Cron Jobs):

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled tasks (`routes/console.php`)

| Task | Frequency | Command |
|------|-----------|---------|
| Prune expired OTP codes | Daily | inline |
| Prune old read notifications | Weekly (Sun 03:00) | `app:prune-notifications` |
| Prune expired support attachments | Hourly | `support:prune-attachments` |
| Prune stale cache tags | Daily | `cache:prune-stale-tags` |
| Monnify reconciliation | Every 5 min | `monnify:reconcile` |
| Roll up analytics KPIs | Hourly | `analytics:rollup-kpis` |
| Prune analytics activity | Daily 04:00 | `analytics:prune-activity` |
| Sync Google Analytics | Daily 05:00 | `analytics:sync-ga` |
| Monitoring heartbeat | Every 5 min | `monitoring:heartbeat` |
| Purge anonymized users | Hourly | `users:purge-anonymized` |
| Expire user tools | Every 5 min | `site-integrations:expire-user-tools` |
| Refresh sitemap cache | Daily 02:30 | inline |
| Database backup (optional) | Daily 02:00 | `app:backup-database` |

The crypto exchange, escrow and peer marketplace were removed on 2026-09-27. If cPanel still has separate cron jobs calling `crypto:*`, `app:expire-crypto-quotes`, `app:warm-crypto-prices` or `wallet:expire-listing-holds`, delete them — those commands no longer exist.

Run manually:

```bash
php artisan app:prune-notifications --days=90
php artisan app:backup-database
php artisan content:scan-legacy-copy   # read-only report of DB copy still mentioning crypto/escrow/marketplace
```

## Backups

### Database (automated)

If `mysqldump` is available on the server, uncomment the backup line in `routes/console.php` or run:

```bash
php artisan app:backup-database
```

Backups are written to `storage/backups/` (not web-accessible).

### cPanel manual backup

1. cPanel → Backup → Download a MySQL Database Backup
2. Store off-server (Google Drive, S3, etc.)
3. Test restore in a staging environment before relying on backups

### What to back up

| Asset | Frequency |
|-------|-----------|
| MySQL database | Daily |
| `storage/app/` (uploads, proofs) | Weekly |
| `.env` (secure vault, not git) | On change |

## Cache

- Production default: `CACHE_STORE=database` (works on shared hosting)
- After deploy with terminal access:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## SEO

- `public/robots.txt` — blocks `/dashboard`, `/admin`, `/api`
- Dynamic sitemap: `GET /sitemap.xml` (static pages, service groups/types, help articles, Website Listings)
- Marketing pages include meta description, canonical URL, and Open Graph tags

Verify after deploy:

```bash
curl -I https://yourdomain.com/sitemap.xml
curl -I https://yourdomain.com/up
```

## Incident response

1. Enable maintenance mode if needed: `php artisan down`
2. Preserve `audit_logs` and `transactions` — never delete ledger rows
3. Use `WalletService::reverseTransaction()` for deposit corrections
4. Check `storage/logs/laravel-*.log` and Sentry (if configured)
5. Restore from backup only after identifying root cause
6. Bring back up: `php artisan up`

## Post-deploy smoke test

1. `GET /` — homepage loads
2. `GET /up` — 200 OK
3. `GET /sitemap.xml` — valid XML
4. Register → OTP email → login → dashboard
5. Admin login → approve test deposit (staging only)

## Payment gateway operations

Wallet creation uses `gateway_operations` for idempotent external API calls. Every provider call must log request/response to the `financial` log channel (secrets redacted).

### Before enabling Flutterwave/Paystack

1. Implement `WalletProviderInterface::createSubaccount()` with timeout, retries, and idempotency key
2. Never create a wallet row until the gateway confirms success OR mark wallet `pending` and reconcile
3. Run reconciliation after incidents:

```bash
php artisan app:reconcile-gateway-wallets
```

### Recovery checklist

| Symptom | Action |
|---------|--------|
| User sees error but wallet exists in gateway | Link `gateway_subaccount_id` manually; mark operation `completed` |
| Wallet in DB but not in gateway | Delete orphan wallet row or retry with same idempotency key |
| Operation stuck `pending` | Check provider dashboard; update `gateway_operations.status` |

## Ledger architecture (future)

v1 uses mutable `wallets.balance` + append-only `transactions`. Post-launch, migrate to double-entry (journal/ledger with calculated balances). Do not delete financial rows — use `status` (`rejected`, `reversed`, `cancelled`).
