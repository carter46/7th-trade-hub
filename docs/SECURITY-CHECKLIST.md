# Security Checklist — Phase 2

Use this before production launch and after major auth/wallet changes.

## Authentication

- [ ] `APP_DEBUG=false` and `APP_ENV=production` on server
- [ ] Login rate limit: 5 attempts per email+IP (`LoginRequest`)
- [ ] Register / forgot-password / reset-password throttled (`routes/auth.php`)
- [ ] OTP verify + resend throttled (6 per minute)
- [ ] OTP codes hashed; failed attempts logged (no code in logs)
- [ ] Session regenerated on login; invalidated on logout
- [ ] `SESSION_ENCRYPT=true` and `SESSION_SECURE_COOKIE=true` with HTTPS

## Authorization

- [ ] Dashboard routes: `auth` + `verified`
- [ ] Admin routes: `auth` + `verified` + `role:admin`
- [ ] Policies enforce ownership on orders, listings, tickets
- [ ] Suspended users blocked (`EnsureNotSuspended` middleware)
- [ ] Wallet financial routes require existing wallet (`has_wallet` middleware)
- [ ] KYC Level 1 required before wallet creation
- [ ] Admin cannot suspend self or remove own admin role

## Financial integrity

- [ ] All balance changes via `WalletService` only
- [ ] Deposit approvals record `approved_by`, `approved_ip`, `approved_device`, `approved_reason`
- [ ] Reversals use `reverseTransaction()` — never edit ledger rows
- [ ] Withdrawal bank account numbers stored encrypted
- [ ] Crypto sell quotes expire (`quoted_at` / `expires_at`)

## HTTP / transport

- [ ] Trust proxies configured for cPanel SSL (`bootstrap/app.php`)
- [ ] Security headers: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, HSTS on HTTPS
- [ ] CSRF on all POST forms (`@csrf`)

## Secrets

- [ ] `.env` not committed; `APP_KEY` set on server
- [ ] SMTP credentials in `.env` only
- [ ] No passwords, OTPs, or full bank numbers in application logs

## Verification smoke tests

1. Register → OTP → login → KYC → create wallet
2. Non-admin gets 403 on `/admin`
3. Suspended user redirected to login
4. User A cannot open User B's support ticket
5. User cannot buy own listing
