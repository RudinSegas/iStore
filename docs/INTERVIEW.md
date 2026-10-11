# Explaining iStore in an interview

## Honest project description

“I rebuilt my earlier iStore student project into a working PHP/MySQL portfolio MVP. It has a Hebrew RTL catalog, product search, account authentication, a session cart, and a transactional demo checkout. This rebuilt version runs locally or on PHP hosting; it does not use AWS.”

Do not claim payment processing, production customers, or an AWS deployment for this rebuild. The original student's historical AWS deployment is separate from this version.

## Trace one request

1. `public/index.php` loads `app/bootstrap.php` to configure the session and open PDO.
2. GET requests select an allowed page and render `app/layout.php`.
3. POST requests go through `app/actions.php`, verify CSRF, validate input and run prepared SQL.
4. Mutations finish with a 303 redirect (POST/redirect/GET). Flash messages survive the redirect once.

## Concepts worth demonstrating live

| Topic | What to show |
| --- | --- |
| SQL injection | Search values go into PDO bound parameters; sort SQL comes from a fixed allowlist. |
| XSS | Dynamic HTML values use `e()`, wrapping `htmlspecialchars`. |
| Passwords | `password_hash` stores a salted one-way hash; `password_verify` checks login. |
| Sessions | Login regenerates the session ID. Cart IDs and quantities remain server-side. |
| Money | Database stores integer agorot; totals multiply integers and format at the UI boundary. |
| Transactions | Order and items are inserted and stock is decremented together, with rollback on failure. |
| Concurrency | `FOR UPDATE` serializes purchases for locked products. Consistent lock ordering reduces deadlock risk. |
| Authorization | Order history filters on the session user ID rather than a supplied user parameter. |
| Duplicate actions | Session token plus unique `orders.checkout_token` prevents duplicate checkout records. |
| Progressive enhancement | Forms work without JS. JS controls mobile navigation and disables duplicate checkout clicks. |

## Database relationships

- One category has many products.
- One user has many orders.
- One order has many order items; each item refers to a product.
- Order items snapshot name and price to retain historical values.
- Contact requests are independent demonstration records.

## Be ready to discuss limitations

The small front-controller approach is intentional for an MVP, but templates and action handlers could be split into controllers/services as scope grows. Add pagination and more selective search for a larger catalog. Add persistent carts, password reset and admin tools next. Public deployment also needs global rate limiting, monitoring, backups, HTTPS, privacy/data retention decisions and a real payment integration.

## Five-minute demo

Open the Hebrew home page → select phones → search “iPhone” → open product → add two units → register → enter a demo address → create a demo order → show its persisted total → logout/login → show order history. Explain the corresponding checkout transaction in `app/actions.php`.
