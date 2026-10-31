# Executed test report — 2026-10-31

All results below refer to this updated release based on the newly uploaded archive. Tests used a disposable local MariaDB database with the supplied seed; no live database or website was touched. These results supersede the previous archive's test report.

| Executed suite | Result |
| --- | --- |
| PHP syntax checks | All source and generated PHP files passed |
| `scripts/check.php` | 7 domain checks passed |
| `tests/ai.php` | 45 unit/transport/validation/fact-rendering checks passed; mocked transport |
| `tests/ai_database.php` | 12 actual SQL/shared-limit/context checks passed; mocked Gemini |
| `tests/smoke.py` | 31 HTTP store checks passed |
| `tests/ai_http.py` | 16 actual handler checks passed; mocked Gemini |
| `tests/security_http.py` | 19 HTTP security/input/rate-limit checks passed |
| `tests/ai_ui.cjs` | 12 Chromium chat checks passed; mocked Gemini |
| `tests/store_ui.cjs` | 17 Chromium mobile/store checks passed |
| `tests/rate_concurrency.py` | 20 simultaneous reservations: exactly 5 accepted and 15 limited |
| Generated hosting layout | Catalog returned HTTP 200 using flattened generated entry points and local PHP/MySQL |
| Apache deployment rules | 6 checks passed: app/config/env/SQL requests denied, public JavaScript available |
| Generated/source consistency | 39 app/public files matched, with only intended entry-point path transformations |

Store checks covered registration, wrong-password and successful login, logout, separate users, session-based private orders, search/categories/price filters, product details, cart updates/removal/stock limits, anonymous checkout rejection, authoritative server totals, duplicate checkout prevention, order persistence and contact demo storage. Browser checks exercised actual forms for registration, add-to-cart and demo checkout; six mobile routes had no page-level horizontal overflow. A mobile order screenshot was visually inspected. No credit-card input or payment service is present.

AI checks covered generated prose and catalog token substitution, short context, history restoration after navigation, comparison, general technical questions, empty matches, SQL-like input, invented IDs/fields/numeric facts, malformed upstream JSON, truncated responses, quota and timeout mapping, missing/invalid CSRF, reset, XSS inert rendering, retry input retention, keyboard/focus behavior and mobile RTL. Rate tests exercised rollback, window resets, global daily caps, concurrent reservations and login limits surviving new sessions.

## Actual Google API verification (not mocked)

Google's models endpoint returned HTTP 200 with the configured default model available. Three actual PHP `generateContent` calls using `gemini-3.5-flash-lite` then succeeded through the application service with real SQL candidates:

| Scenario | Observed result | Single observed latency |
| --- | --- | --- |
| RAM versus SSD and Docker explanation | Generated Hebrew technical answer; no product cards | 5.67 seconds |
| Computer for development/Docker under 5,000 ILS | Generated reasoning; two actual in-budget SQL products | 7.10 seconds |
| Follow-up comparison and missing specifications | Reused those two products; identified missing CPU details | 7.09 seconds |

Actual response text was inspected. Names, prices and listed RAM/SSD fields resolved from SQL, not model-authored prices. Earlier diagnostic attempts encountered connection timeouts and one overly strict validation rejection of a restated user budget. The connection window and paragraph handling were corrected before the successful three-turn sample. This small sample does not establish service availability, future latency or perfect recommendation quality. No retry loop or paid fallback was introduced.

## Environment and limits

Executed on Linux with PHP 8.3.6, MariaDB 10.11, Chromium headless through Playwright and Apache 2.4 access-rule tests. Because this sandbox restricts Unix sockets, the disposable DB/Apache test infrastructure used a temporary socket adaptation; application connections were real TCP. This test-only infrastructure is not part of the release. Apache tests covered access rules, not Apache's PHP module; PHP rendering used the PHP built-in server.

**Not executed:** Docker containers, Windows/XAMPP, Safari/iOS, physical mobile devices, the actual InfinityFree hosting account, live deployment or production load tests. No claim is made that Gemini works from InfinityFree; the secure isolated probe is provided for that verification. Browser viewport checks are not a full accessibility audit. The real Git checkout/history was unavailable, so historic secret exposure remains unknown.

The release contains no private credentials. The uploaded Gemini key should be rotated. See `SECURITY_AUDIT.md` and `INFINITYFREE.md` before publishing.
