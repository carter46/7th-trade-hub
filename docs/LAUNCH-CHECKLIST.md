# Launch checklist

## Infrastructure

- [ ] `composer install --no-dev` on server (or commit vendor for no-SSH hosts)
- [ ] Import/update `database/sql/migration.sql` in phpMyAdmin
- [ ] Configure `.env` per [PRODUCTION-ENV-CHECKLIST.md](PRODUCTION-ENV-CHECKLIST.md)
- [ ] Create admin via `ProductionSeeder` or phpMyAdmin
- [ ] PWA icons in `public/icons/`
- [ ] `storage/` and `bootstrap/cache/` writable
- [ ] Docroot → `public/`
- [ ] Hit `/up` — 200 OK

## Security

- [ ] HTTPS works; cookies secure
- [ ] OTP + login rate limits verified
- [ ] Do **not** run `demo:fresh`, `demo:seed`, or `DemoPlatformSeeder` in production (`SEED_DEMO_DATA` must stay false). Local/staging: see [DEMO-DATA.md](DEMO-DATA.md).

## Core flow smoke test

Automated: `php artisan test` — see [TESTING.md](TESTING.md) (`FullJourneyTest` covers the loop below).

Manual verification on staging:

- [ ] Register → OTP → login
- [ ] KYC submit → admin approve
- [ ] Create wallet
- [ ] Bank deposit → admin approve → balance credited
- [ ] Buy listing → escrow locked
- [ ] Confirm delivery / admin release escrow
- [ ] Withdrawal → admin approve

## Operations

- [ ] Backups scheduled
- [ ] Error reporting configured (optional Sentry)
- [ ] `robots.txt` and sitemap live
