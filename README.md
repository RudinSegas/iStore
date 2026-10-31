# iStore v1.0

Hebrew RTL portfolio store: PHP 8.2+, MySQL 8 / MariaDB 10.6+, HTML/CSS and vanilla JavaScript. Gemini provides conversational technology advice and SQL-grounded shopping recommendations. No real payment processing or credit-card collection. Use fictitious customer/contact/address data and a unique demo password.

## Quick start without Docker

Requirements: PHP CLI/web server with `pdo_mysql`, `curl`, `mbstring`, sessions and HTTPS CA certificates; a local MySQL/MariaDB server. No Composer, npm or Python required to run the store.

1. Extract the release. `app/` and `public/` are the authoritative application sources.
2. Create an empty database named `istore` using your local database administrator. Create an application database user with privileges on that database only.
3. Import, in order, using your database client/phpMyAdmin: `database/schema.sql`, `database/seed.sql`, `database/upgrade_ai_assistant.sql`, `database/upgrade_v1.sql`. **Seed only an empty demo database.** Never reimport the seed into an existing store.
4. Run `php scripts/setup.php`. It creates private `config/config.php` and `config/ai.php` with random secrets, without overwriting existing files. Edit database settings locally. On Windows, enable the listed extensions in the active `php.ini`; Windows execution was not tested.
5. Run `php -S 127.0.0.1:8080 -t public` and visit http://127.0.0.1:8080. Always use `public/` as the document root, never the repository root. The built-in server is for local development only.
6. The store works with AI disabled. To enable AI, follow the private configuration instructions below.

Native PHP does **not** automatically load `.env`. Use private PHP configuration files or exported environment variables. Apache/XAMPP can use the same `public/` document root with PHP 8.2+ and the same extensions. Do not expose the PHP development server to the internet.

## Secure Gemini configuration

The uploaded archive contained a working Gemini key in `.env`. Rotate that key in Google AI Studio. No real key or database password is included in this release. Git metadata was absent, so historical Git exposure could not be determined.

After `php scripts/setup.php`, edit **only your private `config/ai.php`**:

```php
<?php
return [
    'enabled' => true,
    'api_key' => 'REPLACE_PRIVATELY',
    'model' => 'gemini-3.5-flash-lite',
    'rate_secret' => 'REPLACE_WITH_64_RANDOM_HEX_CHARACTERS',
    'timeout_seconds' => 25,
    'connectivity_test_enabled' => false,
    'connectivity_test_token' => '',
];
```

Keep the generated `rate_secret` instead of the placeholder. Defaults are merged from `ai.example.php`. Keep `app_secret` in the private database configuration too. Keys must never go into JavaScript, SQL, Git, screenshots or support messages. Config files should be readable by the PHP process, but not by other hosting users. Never disable TLS verification to fix a connection problem.

The configurable default `gemini-3.5-flash-lite` was confirmed against Google's model documentation and the actual models API on 2026-10-31. Google's pricing documentation lists free-tier input/output for this model, subject to account, region and quotas. Free-tier availability does not guarantee capacity or establish your project's billing tier. Check Google AI Studio before enabling it. The app caps usage at 10 calls/minute and 100/day by default; these are application limits, not promises about Google's quota. There is no automatic paid fallback or model switching.

Official references: [model](https://ai.google.dev/gemini-api/docs/models/gemini-3.5-flash-lite), [pricing](https://ai.google.dev/gemini-api/docs/pricing), [REST API](https://ai.google.dev/api/generate-content), [structured output](https://ai.google.dev/gemini-api/docs/structured-output).

## Optional Docker

1. Copy `.env.example` to `.env` locally. Set unique `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `APP_SECRET`, and, if enabling AI, `GEMINI_API_KEY` and a random `AI_RATE_SECRET`. Generate random secrets with `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`.
2. Run `docker compose up --build -d`.
3. Visit http://127.0.0.1:8080 (or `APP_PORT`).
4. Stop with `docker compose down`. The named database volume persists. Do not use `down -v` unless intentionally deleting demo data.

Initialization scripts run only for a new database volume. Existing installations must separately import `upgrade_ai_assistant.sql` if not already applied and `upgrade_v1.sql`. Both use additive `CREATE TABLE IF NOT EXISTS`; they do not reset users, products or orders. Docker was reviewed statically, not executed in this environment.

## InfinityFree

Follow [docs/INFINITYFREE.md](docs/INFINITYFREE.md). The release contains a generated `build/infinityfree/htdocs/` with the existing flattened hosting layout. All behavior comes from `app/` and `public/`; never maintain two hand-edited implementations. Regenerate after source changes with `php scripts/build-infinityfree.php` into a **new** directory. The generator refuses to overwrite existing directories or copy private configuration.

No deployment, upload to the live host, GitHub push or live database change was performed. Local Google connectivity was tested; InfinityFree connectivity remains unverified and requires the included isolated opt-in probe on that host.

## Features and boundaries

- Responsive Hebrew RTL catalog, escaped search, category/price filtering, sorting and pagination.
- Registration/login, hardened cookies and CSRF, shared login/registration/contact limits.
- Guest cart, stock validation, transactional demo checkout, idempotent order token, private order history.
- One Gemini request per chat turn: PHP filters MySQL candidates; Gemini explains and compares. General technology questions receive generated answers, not canned product-list text.
- Bounded session context (eight messages, 30-minute expiry), restored chat history, reset and retry UX.
- Catalog names/prices/specifications/stock use server-resolved fact tokens and server-built product cards/links. The model cannot generate SQL or modify orders.
- Safe error messages for quota, timeouts, invalid output and connectivity failure. Catalog shopping remains available when AI fails.

See [AI architecture](docs/AI_ASSISTANT.md), [security audit](docs/SECURITY_AUDIT.md), [actual test report](docs/TEST_REPORT.md), and [test instructions](tests/README.md). This is a portfolio demo, not a production payment or fulfillment system.

## Build a new sanitized release

Generate a fresh deployment tree, then run `python3 scripts/package-release.py /absolute/path/iStore-v1.0.zip`. Python is used only for optional release packaging/security audit/testing, not the application. The packager excludes private config, `.env`, Git metadata, logs and backups and stops on recognizable Google key material. Review custom secrets too; no pattern scan can prove their absence.
