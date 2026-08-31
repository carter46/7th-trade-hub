# Domain checkout flows

## Standalone domain product

1. Product page: search SLD + TLD → domain quote.
2. Continue to checkout with `quote_token`, `domain_fqdn`, `quoted_price`.
3. Checkout redirects back if quote missing.
4. Purchase: one order line at quoted retail NGN.

## Website package

1. Product page: pick plan → checkout with `?variant=`.
2. Checkout: read-only plan summary; quantity hidden (1).
3. **Required** domain choice:
   - **Buy**: quote token + second order line (domain registration product).
   - **Connect**: FQDN only; one order line; no domain fee.
4. `domain_mode=none` rejected server-side.

## Legacy public checkout

`/checkout/platform/{slug}` GET/POST redirect to dashboard checkout so domain rules always apply.

## Order options (internal)

Domain lines include normalized keys: `provider`, `provider_cost`, `provider_currency`, `retail_price`, `premium`, `purchase_type`, `quote_id`, `domain_fqdn`, `tld`.

Customer order views show title + price only.
