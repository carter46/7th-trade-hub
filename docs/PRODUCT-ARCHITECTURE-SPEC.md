# 7th Trade Hub — Product & Architecture Specification

## Vision

A digital services hub where users hold a **platform wallet (NGN)**, **deposit** funds, buy admin-curated platform services (VPN and proxy plans, domains, websites, social growth, receipts and business documents), and **withdraw** to bank.

The crypto exchange (Sell Crypto / OTC), escrow and the peer-to-peer marketplace (listings, sales, watchlist, messages, reviews, platform fee) were permanently removed on 2026-09-27. Historical ledger rows from those features stay as read-only history with "Legacy" labels.

## Naming

| User sees | System uses internally |
|-----------|------------------------|
| Deposit Money, Deposit History | WalletFunding, wallet_fundings |
| My Orders | orders with `source = 'platform'` |

## Module boundaries

- **Catalog** — admin-owned platform products (service categories → services → products → variants), Website Listings, platform checkout
- **Wallet** — wallet, fundings (deposits), withdrawals, holds, ledger, KYC
- **Admin** — users, approvals, orders, reports, settings, audit, platform catalog edits
- **Support** — tickets (categorized), replies, notifications

Modules interact via services and events only. See [TWO-CATALOG.md](TWO-CATALOG.md) for the catalog structure.

## Core user journey (v1)

1. Register → verify email (OTP) → login
2. Complete profile → submit KYC Level 1
3. Admin approves KYC
4. User clicks **Create Wallet** (payment provider subaccount provisioned)
5. **Deposit** via Monnify checkout, reserved account or bank transfer
6. NGN credited to wallet (automatically for Monnify, after admin approval for manual transfers)
7. Browse services → buy with wallet, payment gateway or manual bank transfer
8. Admin / provider fulfils the order → it appears in My Orders and My Tools
9. View transaction history → withdraw to bank
10. Open support ticket (category required)

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
| Card / bank checkout | monnify_checkout | Yes |
| Reserved account | monnify_reserved | Yes |
| Bank Transfer | bank | Yes |
| *(legacy)* Sell Crypto | crypto | Removed — existing rows display as "Legacy credit" |

## Financial rules

- Wallet holds **NGN only** (balance + locked_balance)
- All balance changes via ledger; **never edit or delete** ledger rows — use reversal entries
- `locked_balance` equals the sum of active holds (withdrawal / compliance)
- Legacy transaction types (`escrow_lock`, `escrow_release`, `refund`, `platform_fee`, `listing_hold`, `listing_hold_release`) are never created any more
- Funding approvals record approver, IP, device, reason

## Support ticket categories

payment, withdrawal, wallet, order, kyc, technical, other

## Launch phases

0. Product + technical spec documents  
1. Production infrastructure  
2. Production security  
3. Wallet money loop (deposit, purchase, withdrawal)  
4. Admin platform  
5. Platform catalog + checkout  
6. Operations (backups, monitoring, SEO)  
7. Testing (full journeys, CI)

## Out of scope

- Crypto trading, OTC or custody of any kind
- Peer-to-peer listings, escrow or seller payouts
