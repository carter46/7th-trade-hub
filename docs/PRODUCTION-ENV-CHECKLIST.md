# Production environment checklist

## Required

- [ ] `APP_KEY` — generate: `php artisan key:generate --show`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://yourdomain.com` (no trailing slash)
- [ ] `DB_*` — MySQL credentials
- [ ] `MAIL_*` — SMTP for OTP emails
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_ENCRYPT=true`
- [ ] `QUEUE_CONNECTION=sync` (unless worker confirmed)

## Mail smoke test

1. Register a test account
2. Confirm OTP email arrives
3. Verify and reach dashboard

## Optional

- `ADMIN_EMAIL` / `ADMIN_PASSWORD` for one-time `ProductionSeeder`
- `WALLET_PLATFORM_CRYPTO_ADDRESS` for crypto sell instructions
- `SENTRY_LARAVEL_DSN` — install `sentry/sentry-laravel` first
- `LOG_SLACK_WEBHOOK_URL` — critical error alerts
- Cron: `* * * * * php artisan schedule:run` (see [OPERATIONS.md](OPERATIONS.md))
