# 7th Trade Hub — Technical Implementation Specification

See [PRODUCT-ARCHITECTURE-SPEC.md](PRODUCT-ARCHITECTURE-SPEC.md) for business rules and journeys.

## Module layout

```
app/Modules/Wallet/
app/Modules/Catalog/
app/Modules/Admin/
app/Modules/Support/
```

The `Marketplace` module and all crypto exchange code were removed on 2026-09-27.

## Services

### Wallet module

- `WalletService` — creditFromFunding, debitForPlatformPurchase, creditPlatformFromGatewaySale, lockForWithdrawal, debitForWithdrawal, adminAdjust, **reverseTransaction**, holds
- `WalletProvisioningService` + `WalletProviderInterface` — ManualProvider (v1 default)

### Catalog module

- `PlatformCheckoutService` — wallet / gateway / manual bank transfer checkout for platform products
- `CatalogBrowseService` — public and dashboard browse, canonical product URLs

## Key controllers

| Controller | Module | Notes |
|------------|--------|-------|
| DepositController | Wallet | UI "Deposit"; persists WalletFunding |
| WalletController | Wallet | create wallet post-KYC |
| KycController | Wallet | submit Level 1 |
| WithdrawalController | Wallet | bank payout |
| ServiceController | Catalog | public services browse |
| WebsiteListingController | Catalog | Website Listings (platform website packages) |
| DiscoverServicesController | Dashboard | dashboard browse + checkout |
| SupportTicketController | Support | categorized tickets |
| Admin/* | Admin | approvals, orders, KYC, catalog |

## Removal migrations (2026-09-27)

- `2026_09_27_000001_remove_crypto_exchange` — aborts if any crypto sell request is still open or uncredited, or an incoming crypto deposit was never approved/ignored; then drops the seven crypto tables. No other row is changed.
- `2026_09_27_000002_remove_escrow_and_marketplace` — aborts on unsettled escrows, active escrow/listing holds, or open marketplace orders; drops only the foreign keys on `orders.listing_id` and `transactions.escrow_id` (values kept); drops the marketplace tables.

**User data is never changed or deleted by either migration:** users, wallets, wallet holds, fundings, transactions, orders, order items, notifications, favorites and support tickets stay as they are, and so do catalog and site-setting rows (the retired `trust-escrow` category is hidden by `ServiceCategory::system()`). Legacy enum cases (`TransactionType`, `WalletHoldReason`) keep historical rows loadable.

Both are one-way (`down()` throws). Take and verify a restorable backup first.

## Routes

- `routes/web.php` — public pages, services, dashboard, admin. Old crypto/marketplace URLs are GET-only 301 redirect closures named `*.legacy.*`, each inside its original middleware group.
- `routes/api.php` — Sanctum-protected JSON (transactions, notifications)

## Testing

- `tests/Feature/Wallet/` — deposit, KYC wallet gate, withdrawal
- `tests/Feature/Admin/` — KYC, funding approval, reverseTransaction
- `tests/Feature/CryptoRemovalTest.php`, `MarketplaceRemovalTest.php`, `LegacyRecordsGuardTest.php` — removed features stay removed
- `.github/workflows/tests.yml` — composer, npm build, php artisan test

## Deployment

See [DEPLOYMENT-CPANEL.md](DEPLOYMENT-CPANEL.md), [PRODUCTION-ENV-CHECKLIST.md](PRODUCTION-ENV-CHECKLIST.md), [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md).
