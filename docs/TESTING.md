# Testing guide

## Automated tests

Run the full suite:

```bash
composer install
php artisan test
```

CI runs on every push/PR via `.github/workflows/tests.yml` (Composer, npm build, PHPUnit).

### Coverage by area

| Area | Test file(s) |
|------|----------------|
| Funding idempotency | `FundingIdempotencyTest` |
| Withdrawal reject guard | `WithdrawalRejectTest` |
| KYC → wallet gate | `Wallet\KycWalletGateTest` |
| Bank deposit | `Wallet\DepositFlowTest` |
| Withdrawal | `Wallet\WithdrawalFlowTest` |
| Deposit reverse | `FundingApprovalTest` |
| Platform checkout (wallet / gateway / manual bank) | `MarketplaceRemovalTest`, `CatalogGatewayCheckoutGatingTest`, `ManualBankTransferOrderTest` |
| Removed features stay removed | `CryptoRemovalTest`, `MarketplaceRemovalTest`, `LegacyRecordsGuardTest` |
| Authorization | `AuthorizationPolicyTest`, `AdminAccessTest` |
| Operations | `SitemapTest`, `HealthCheckTest`, `PruneNotificationsCommandTest` |
| Auth | `RegistrationTest`, `EmailVerificationTest`, etc. |

The crypto exchange, escrow and peer marketplace were removed on 2026-09-27. `CryptoRemovalTest` and `MarketplaceRemovalTest` check the 301 redirects, that no routes or menu entries remain, and that public pages carry no crypto/escrow copy. `LegacyRecordsGuardTest` makes sure nothing creates the legacy transaction types or `method = crypto` fundings, and that historical rows still render with "Legacy" labels.

## Manual QA (pre-launch)

Run in **staging** with real SMTP before production.

### Desktop browsers

- [ ] Chrome — register, OTP, dashboard, buy flow
- [ ] Firefox — wallet, deposit form, admin approvals
- [ ] Safari / Edge — services browse, product detail, Website Listings

### Mobile

- [ ] Responsive layout on dashboard sidebar and services pages
- [ ] PWA install prompt (if icons present)
- [ ] Touch targets on Buy / Checkout buttons

### Core journey (manual)

1. Register new user → receive OTP email → verify
2. Submit KYC → admin approves
3. Create wallet
4. Bank deposit → admin approves → balance updates
5. Buy a platform service from `/dashboard/services` with the wallet → order appears in My Orders
6. Admin fulfils the order → user sees it in My Tools / My Orders
7. User requests withdrawal → admin approves

### Admin

- [ ] Confirm / reject a manual bank transfer order
- [ ] Reverse approved deposit → balance debited
- [ ] Audit logs show all actions

### Edge cases

- [ ] Purchase without balance → redirect to deposit
- [ ] Old `/marketplace`, `/exchange`, `/dashboard/orders` URLs → 301 redirect
- [ ] Suspended user → logged out on next request

## Load testing (optional)

For launch traffic expectations, use a tool like [k6](https://k6.io/) or Apache Bench against:

- `GET /` (homepage)
- `GET /services`
- `GET /up` (health)

Example:

```bash
ab -n 200 -c 10 https://staging.yourdomain.com/up
```

Target: p95 < 2s on shared hosting for public pages. Dashboard/admin under auth will be slower — acceptable.

## Before each release

```bash
php artisan test
npm run build
composer audit
```

See [LAUNCH-CHECKLIST.md](LAUNCH-CHECKLIST.md) for deploy steps.
