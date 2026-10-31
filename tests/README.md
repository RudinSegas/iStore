# Running tests

Use a disposable database initialized with all four SQL files listed in README. Integration tests create users/contact records/orders and decrement stock. Never run them against live data.

Requirements: PHP with application extensions; Python 3 standard library for HTTP tests; optionally Playwright/Chromium for browser tests. These are testing tools, not application dependencies.

Configure private local `config/config.php` for the disposable database. For mocked tests only, set private `config/ai.php` to enabled, a fake nonempty API key, a random 32+ character `rate_secret`, and high test limits (`session_per_minute=100`, `ip_per_minute=200`, `global_calls_per_minute=300`, `global_calls_per_day=1000`). Restore production limits afterwards. **Never use a real key with the test router**; it injects a mock model and never needs one.

```sh
php scripts/check.php
php tests/ai.php
php tests/ai_database.php
php -S 127.0.0.1:8088 -t public tests/router.php
```

In another terminal:

```sh
python3 tests/smoke.py http://127.0.0.1:8088
python3 tests/ai_http.py http://127.0.0.1:8088
python3 tests/rate_concurrency.py
python3 tests/security_http.py http://127.0.0.1:8088
node tests/ai_ui.cjs
node tests/store_ui.cjs
```

Browser tests require `playwright` accessible to Node and a browser installation; set `BROWSER_EXECUTABLE` if using a custom Chromium and `ISTORE_BASE_URL` if needed. The store browser script assumes a fresh test database or fewer than eight registrations/hour from its IP. Rate-limit tests intentionally consume quotas: reinitialize your disposable database when rerunning the complete suite. The browser screenshots are temporary test artifacts, not release assets.

The router is outside `public/` and never included in generated hosting files. Production `api/chat.php` has no client-selectable mock mode. `ai.php` tests validation/transport/factual rendering without DB/network. `ai_database.php` uses mocked Gemini and actual SQL. HTTP/browser tests use the real PHP handler with injected model responses. `rate_concurrency.py` runs 20 simultaneous PHP reservations and expects exactly five successes.

Live Gemini and InfinityFree checks are intentionally separate from the automated mock suite. Use the opt-in isolated probe documented in `docs/INFINITYFREE.md` with your own credentials. Never assume successful mock tests verify hosting connectivity or model quality.
