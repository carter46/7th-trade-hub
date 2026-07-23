# Demo Data

Realistic, timeline-driven demo data for local/staging demos. **Never run in production.**

## Safety

| Guard | Rule |
|-------|------|
| `APP_ENV=production` | `demo:seed`, `demo:fresh`, and `DemoPlatformSeeder` refuse to run |
| `SEED_DEMO_DATA` | Required `true` outside `local` / `testing` (see `.env.example`) |
| Hostinger / live | Keep `SEED_DEMO_DATA=false`; do not run `demo:fresh` |

## Commands

```bash
# Wipe DB, run core seeders + DemoPlatformSeeder, print checklist (preferred)
php artisan demo:fresh --force

# Seed into an empty DB (refuses if alice@example.com already exists)
php artisan demo:seed --force
```

`demo:seed` also runs `MarketplaceListingSeeder` so listing volume stays near ~100. Prefer `demo:fresh` for demos — re-running `demo:seed` is not idempotent.

Without `--force`, both commands ask for confirmation.

## Volume caps (relationships over bulk)

| Entity | Target |
|--------|--------|
| Members + ACL admins | ~20–30 |
| Listings | ~100 (status mix) |
| Orders | ~100 |
| Escrows | ~50 (successful / waiting / disputed / refunded / expired) |
| Support tickets | ~40 (scripted threads) |
| KYC submissions | ~20 |
| Transactions | ~300 (valid `TransactionType` only, NGN) |

Primary personas span up to **~8 months** of dated events. Charts should show gradual growth and weekend dips — not a single seed-day spike.

## Member personas

Password for all: `password`

| Email | Arc |
|-------|-----|
| `alice@example.com` | Buyer: register → KYC → fund → purchase → release → review → support |
| `michael@example.com` | Seller-heavy, pending KYC, escrow history |
| `sarah.design@example.com` | Seller with approved KYC and payouts |
| `john@example.com` | Rejected KYC + appeal |
| `emily@example.com` | Brand-new empty dashboards |
| `memberN@example.com` | Fillers (staggered registration / KYC mix) |

## Admin personas (ACL)

Password for all: `password`

| Email | Role | Focus |
|-------|------|--------|
| `super.admin@example.com` | `admin` | Full permissions + `admins.manage` |
| `finance.admin@example.com` | `demo_finance` | `finance.manage`, `analytics.view` |
| `compliance.admin@example.com` | `demo_compliance` | `compliance.manage`, `users.manage` |
| `support.admin@example.com` | `demo_support` | `support.manage` |
| `moderator@example.com` | `demo_moderator` | `catalog.manage` |

Limited admins use dedicated Spatie roles (not the full `admin` role) so section ACL is testable. Admin routes accept `admin|demo_finance|demo_compliance|demo_support|demo_moderator`.

## Escrow arcs

| Arc | Outcome |
|-----|---------|
| Successful | Locked → delivered → confirmed → released (fee → platform wallet) |
| Waiting | Locked, awaiting seller delivery |
| Disputed (open) | Escrow `disputed` for admin queue |
| Dispute → refund | Lock → admin refund → `refunded` (order cancelled) |
| Expired / auto-release | Released with auto-release notes |

## Architecture

```
database/seeders/Demo/
  DemoPlatformSeeder.php      # orchestrator, guard, assertConsistency, checklist
  DemoUsersSeeder.php
  DemoAdminsSeeder.php
  DemoWalletSeeder.php
  DemoKycSeeder.php
  DemoMarketplaceSeeder.php
  DemoOrdersEscrowSeeder.php
  DemoSupportSeeder.php
  DemoNotificationsSeeder.php
  DemoAuditSeeder.php
  DemoAnalyticsSeeder.php
  Support/                    # catalogs, timeline, conversation scripts
```

Deprecated: `Database\Seeders\DemoDataSeeder` → thin wrapper around `DemoPlatformSeeder`.

## Out of scope (v1)

- Real GA Data API traffic
- Fake encrypted session / login-history UI
- Real uploaded binaries (JSON doc paths + catalog media)
- 10k+ synthetic users
