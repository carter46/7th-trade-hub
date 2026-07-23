# Demo Data

Realistic, timeline-driven demo data for pre-launch / staging demos.

## Safety model (environment ≠ demo permission)

| Flag | Purpose |
|------|---------|
| `APP_ENV` | Laravel behaviour (cache, debug, etc.) — may stay `production` pre-launch |
| `ALLOW_DEMO_DATA` | Whether fake rows may be inserted / cleared |
| `ALLOW_DESTRUCTIVE_SEEDERS` | Required for `demo:fresh` (full DB wipe) when `APP_ENV=production` |
| `SEED_DEMO_DATA` | Alias for `ALLOW_DEMO_DATA` (backwards compatible) |

Local (`APP_ENV=local`) defaults `ALLOW_DEMO_DATA` on unless you set it false.

**Live launch:** set `ALLOW_DEMO_DATA=false` permanently after `demo:clear`.

## Commands

```bash
# Insert demo into current DB (no wipe) — works with APP_ENV=production if ALLOW_DEMO_DATA=true
php artisan demo:seed --force

# Remove ONLY tagged demo rows (keeps real admins, roles, catalog, settings)
php artisan demo:clear --force
php artisan analytics:rollup-kpis

# Full wipe + reseed (local/staging, or production + ALLOW_DESTRUCTIVE_SEEDERS=true)
php artisan demo:fresh --force
```

Without `--force`, commands ask for confirmation and require typing `YES`.

## Pre-launch production (your Hostinger case)

```env
APP_ENV=production
ALLOW_DEMO_DATA=true
ALLOW_DESTRUCTIVE_SEEDERS=false
```

```bash
php artisan config:clear
php artisan migrate --force
php artisan demo:seed --force
```

Overview should then show KYC, tickets, escrows, charts.

### Launch day cleanup

```bash
php artisan demo:clear --force
php artisan analytics:rollup-kpis
# then set ALLOW_DEMO_DATA=false and never re-enable on live
```

## How tagging works

Each `demo:seed` / `DemoPlatformSeeder` run creates a `demo_batches` row and tracks created model IDs in `demo_batch_records`.  
`demo:clear` deletes those records in FK-safe order (activity/KPIs for demo users included).

## Volume caps

| Entity | Target |
|--------|--------|
| Members + ACL admins | ~20–30 |
| Listings | ~100 |
| Orders | ~100 |
| Escrows | ~50 (successful / waiting / disputed / refunded / expired) |
| Support tickets | ~40 |
| KYC | ~20 |
| Transactions | ~300 |

## Member personas (password `password`)

| Email | Arc |
|-------|-----|
| `alice@example.com` | Buyer journey |
| `michael@example.com` | Seller-heavy |
| `sarah.design@example.com` | Seller + payouts |
| `john@example.com` | Rejected KYC + appeal |
| `emily@example.com` | Empty new user |
| `memberN@example.com` | Fillers |

## Admin personas (password `password`)

| Email | Role |
|-------|------|
| `super.admin@example.com` | `admin` |
| `finance.admin@example.com` | `demo_finance` |
| `compliance.admin@example.com` | `demo_compliance` |
| `support.admin@example.com` | `demo_support` |
| `moderator@example.com` | `demo_moderator` |

## Architecture

```
database/seeders/Demo/DemoPlatformSeeder.php   # orchestrator
  DemoUsers / Admins / Kyc / Wallet / Marketplace /
  OrdersEscrow / Support / Notifications / Audit / Analytics
app/Support/Demo/DemoGate.php                  # ALLOW_DEMO_DATA checks
app/Support/Demo/DemoBatchTracker.php          # tagging + clear
```

Deprecated: `DemoDataSeeder` → wrapper around `DemoPlatformSeeder`.
