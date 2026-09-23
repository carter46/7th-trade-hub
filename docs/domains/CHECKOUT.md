# Domain checkout flows

## Standalone domain product

1. Product page: search SLD + TLD → domain quote.
2. Continue to checkout with `quote_token`, `domain_fqdn`, `quoted_price`.
3. Checkout redirects back if quote missing.
4. Purchase: one order line at quoted retail NGN.

## Website package

1. Product page: pick plan → checkout with `?variant=`.
2. Checkout: read-only plan summary; quantity hidden (1).
3. **Required** domain choice (always visible):
   - **Buy**: quote token + **second** order line (domain registration product). Website line stays at plan price only.
   - **Connect**: FQDN + DNS ack; one website order line; **no domain fee**.
4. `domain_mode=none` rejected server-side.
5. Showing Buy/Connect never auto-adds a domain charge — only explicit **Buy** creates the domain line.

## Provider vs manual commerce mode

Resolved only by `DomainCommerceModeResolver` (enabled domain providers nonempty → `provider`, else `manual`):

| Mode | Buy New Domain |
|------|----------------|
| `provider` | Live registry TLDs, availability check, markup/FX retail, auto-register when enabled |
| `manual` | Admin prices on Domain product Allowed extensions; no provider HTTP; locked quote retail; `pending_manual` fulfillment |

Manual prices are configured on the **domain-registration** product form and apply to standalone domain checkout and website “Buy New Domain”.

## Legacy public checkout

`/checkout/platform/{slug}` GET/POST redirect to dashboard checkout so domain rules always apply.

## Order options (internal)

Domain lines include: `domain_fqdn`, `tld`, `domain_mode`, `retail_price`, `domain_quote_id`, `domain_fulfillment` (`provider`|`manual`), registrant contact.

Customer order views show title + price only.
