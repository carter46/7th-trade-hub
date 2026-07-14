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
| Full money loop (HTTP) | `FullJourneyTest` |
| Funding idempotency | `FundingIdempotencyTest` |
| Withdrawal reject guard | `WithdrawalRejectTest` |
| Checkout atomicity | `CheckoutAtomicityTest` |
| KYC → wallet gate | `KycWalletGateTest` |
| Bank deposit | `DepositFlowTest` |
| Crypto quote expiry | `CryptoSellQuoteExpiryTest`, `ExpireCryptoQuotesCommandTest` |
| Crypto sell admin | `CryptoSellApprovalTest`, `CryptoSellRejectTest` |
| Checkout + escrow release | `CheckoutEscrowFlowTest` |
| Escrow refund | `EscrowRefundTest` |
| Withdrawal | `WithdrawalFlowTest` |
| Deposit reverse | `FundingApprovalTest` |
| Listing review flow | `ListingReviewFlowTest`, `ListingVersionEditTest` |
| Reviews, watchlist, messages | `ReviewFlowTest`, `WatchlistTest`, `MessageFlowTest` |
| Authorization | `AuthorizationPolicyTest`, `AdminAccessTest` |
| Operations | `SitemapTest`, `HealthCheckTest`, `PruneNotificationsCommandTest` |
| Auth | `RegistrationTest`, `EmailVerificationTest`, etc. |

## Manual QA (pre-launch)

Run in **staging** with real SMTP before production.

### Desktop browsers

- [ ] Chrome — register, OTP, dashboard, buy flow
- [ ] Firefox — wallet, deposit form, admin approvals
- [ ] Safari / Edge — marketplace browse, listing detail

### Mobile

- [ ] Responsive layout on dashboard sidebar and marketplace
- [ ] PWA install prompt (if icons present)
- [ ] Touch targets on Buy / Confirm delivery buttons

### Core journey (manual)

1. Register new user → receive OTP email → verify
2. Submit KYC → admin approves
3. Create wallet
4. Bank deposit → admin approves → balance updates
5. Seller publishes listing → appears on `/marketplace`
6. Buyer purchases → escrow locked
7. Buyer confirms delivery → seller credited (minus fee)
8. Buyer leaves review
9. Seller requests withdrawal → admin approves

### Admin

- [ ] Reject listing with notes → seller can edit and resubmit
- [ ] Reject crypto sell → user sees rejected status
- [ ] Reverse approved deposit → balance debited
- [ ] Refund escrow → buyer balance restored
- [ ] Audit logs show all actions

### Edge cases

- [ ] Buy own listing → 403
- [ ] Purchase without balance → redirect to deposit
- [ ] Expired crypto quote → cannot approve
- [ ] Suspended user → logged out on next request

## Load testing (optional)

For launch traffic expectations, use a tool like [k6](https://k6.io/) or Apache Bench against:

- `GET /` (homepage + crypto ticker)
- `GET /marketplace`
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
