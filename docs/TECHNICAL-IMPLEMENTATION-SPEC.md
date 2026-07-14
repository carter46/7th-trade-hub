# 7th Trade Hub — Technical Implementation Specification

See [PRODUCT-ARCHITECTURE-SPEC.md](PRODUCT-ARCHITECTURE-SPEC.md) for business rules and journeys.

## Module layout

```
app/Modules/Wallet/
app/Modules/Marketplace/
app/Modules/Admin/
app/Modules/Support/
```

## Services

### Wallet module

- `WalletService` — creditFromFunding, debitForPurchase, releaseEscrow, refundEscrow, lockForWithdrawal, debitForWithdrawal, adminAdjust, **reverseTransaction**
- `WalletProvisioningService` + `WalletProviderInterface` — ManualProvider (v1 default)
- `CryptoPriceService` — CoinGecko quotes, homepage ticker cache

### Marketplace module

- `CheckoutService` — buy flow, creates Escrow, calls WalletService

## Key controllers

| Controller | Module | Notes |
|------------|--------|-------|
| DepositController | Wallet | UI "Deposit"; persists WalletFunding |
| CryptoSellController | Wallet | OTC sell, quote expiry |
| WalletController | Wallet | create wallet post-KYC |
| KycController | Wallet | submit Level 1 |
| WithdrawalController | Wallet | bank payout |
| MarketplaceController | Marketplace | public browse |
| ListingController | Marketplace | seller CRUD + versions |
| CheckoutController | Marketplace | buy + escrow |
| SupportTicketController | Support | categorized tickets |
| Admin/* | Admin | approvals, escrow, KYC |

## Migration order

1. Users: kyc_level, profile fields, is_suspended, terms_accepted_at
2. Refactor wallets → NGN balance, locked_balance, currency, gateway_subaccount_id, status
3. wallet_fundings (+ approval audit columns)
4. crypto_sell_requests (quoted_at, expires_at)
5. escrows
6. kyc_submissions
7. withdrawals
8. categories; listings user_id, status, category_id
9. listing_versions
10. transactions ledger FKs + reverses_transaction_id
11. support_tickets category, replies
12. messages, audit_logs, system_settings
13. Default roles seed
14. Sync database/sql/migration.sql

## Routes

- `routes/web.php` — dashboard, admin, marketplace
- `routes/api.php` — Sanctum-protected JSON (transactions, notifications)

## Testing

- `tests/Feature/Marketplace/` — deposit, crypto quote expiry, escrow, withdrawal
- `tests/Feature/Admin/` — KYC, funding approval, reverseTransaction
- `.github/workflows/tests.yml` — composer, npm build, php artisan test

## Deployment

See [DEPLOYMENT-CPANEL.md](DEPLOYMENT-CPANEL.md), [PRODUCTION-ENV-CHECKLIST.md](PRODUCTION-ENV-CHECKLIST.md), [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md).
