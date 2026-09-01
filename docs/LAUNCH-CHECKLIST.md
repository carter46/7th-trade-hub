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
- [ ] Do **not** leave `ALLOW_DEMO_DATA=true` on live after launch. Pre-launch: see [DEMO-DATA.md](DEMO-DATA.md). Launch: `demo:clear --force` then set `ALLOW_DEMO_DATA=false`.

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

## Monnify disbursements (withdrawals)

- [ ] With MFA enabled (default): after admin **Approve & send**, enter the Monnify OTP on **Admin → Withdrawals → detail** (not the Monnify dashboard UI).
- [ ] If Monnify summary status is `EXPIRED`, do **not** reuse an old OTP — use **Retry payout** for a new `WPO-*` reference.
- [ ] Optional production automation: email Monnify support to disable disbursement MFA and whitelist server IP (user password + email OTP on withdrawal requests still required).

## Operations

- [ ] Backups scheduled
- [ ] Error reporting configured (optional Sentry)
- [ ] `robots.txt` and sitemap live
