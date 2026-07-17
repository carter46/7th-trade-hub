# 7th Trade Hub — Product & Architecture Specification

## Vision

A real marketplace and investment platform where users hold a **platform wallet (NGN)**, **deposit** funds, buy services and products through **escrow**, and **withdraw** to bank. Crypto is one deposit method (OTC sell), not the financial center.

## Naming

| User sees | System uses internally |
|-----------|------------------------|
| Deposit Money, Deposit History | WalletFunding, wallet_fundings |

## Module boundaries

- **Marketplace** — listings, orders, reviews, categories, search, messages
- **Catalog** — admin-owned platform products (services, templates, website packages), exchange rates, platform checkout
- **Wallet** — wallet, fundings (deposits), withdrawals, ledger, escrows, crypto sell OTC, KYC
- **Admin** — users, approvals, reports, settings, audit, platform catalog CRUD
- **Support** — tickets (categorized), replies, notifications

Modules interact via services and events only.

**Two catalogs:** Platform Products ≠ Marketplace Listings. See [TWO-CATALOG.md](TWO-CATALOG.md).

## Core user journey (v1)

1. Register → verify email (OTP) → login
2. Complete profile → submit KYC Level 1
3. Admin approves KYC
4. User clicks **Create Wallet** (payment provider subaccount provisioned)
5. **Deposit** via bank transfer (proof) or crypto sell (OTC quote, 15 min validity)
6. Admin approves funding → NGN credited to wallet
7. Browse marketplace → buy listing → escrow locks NGN
8. Seller delivers → buyer confirms or admin resolves → escrow released
9. Seller creates listing (draft version → admin review → publish)
10. View transaction history → withdraw to bank
11. Open support ticket (category required)

## KYC levels

| Level | Name | v1 |
|-------|------|-----|
| 0 | None | Default |
| 1 | Basic | Required for wallet |
| 2 | Identity | Future |
| 3 | Address | Future |
| 4 | Enhanced | Future |

## Deposit methods

| UI | Internal method | v1 |
|----|-----------------|-----|
| Bank Transfer | bank | Yes |
| Sell Crypto | crypto | Yes (OTC) |
| Card | card | Future |
| Paystack | paystack | Future |
| Flutterwave | flutterwave | Future |

## Financial rules

- Wallet holds **NGN only** (balance + locked_balance)
- All balance changes via ledger; **never edit or delete** ledger rows — use reversal entries
- Escrow is mandatory for platform-mediated purchases
- Crypto sell quotes expire after 15 minutes
- Funding approvals record approver, IP, device, reason

## Support ticket categories

payment, withdrawal, wallet, marketplace, listing, order, kyc, crypto_sell, technical, other

## Launch phases

0. Product + technical spec documents  
1. Production infrastructure  
2. Production security  
3. Core marketplace flow (money loop)  
4. Admin platform  
5. Marketplace features (draft versions, search, notifications)  
6. Operations (backups, monitoring, SEO)  
7. Testing (full journeys, CI)

## Out of scope for v1

- Order book / crypto matching engine
- Crypto balances on wallet row
- Automated blockchain custody
