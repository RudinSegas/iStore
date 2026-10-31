# Release changes

Based exclusively on the newly attached archive. Private config and stale generated snapshots are omitted; hosting output is regenerated from source.

## Added

- `app/catalog-page.php`
- `app/rate-limit.php`
- `database/upgrade_v1.sql`
- `docs/CHANGES.md`
- `docs/INFINITYFREE.md`
- `scripts/audit-secrets.py`
- `scripts/package-release.py`
- `scripts/setup.php`
- `tests/README.md`
- `tests/rate_concurrency.py`
- `tests/security_http.py`
- `tests/store_ui.cjs`

## Modified

- `.dockerignore`
- `.env.example`
- `README.md`
- `START_HERE.md`
- `app/actions.php`
- `app/ai/assistant.php`
- `app/ai/catalog.php`
- `app/ai/errors.php`
- `app/ai/gemini.php`
- `app/ai/http.php`
- `app/bootstrap.php`
- `app/chat-widget.php`
- `app/functions.php`
- `app/layout.php`
- `app/product-cards.php`
- `compose.yaml`
- `config/ai.example.php`
- `config/config.example.php`
- `docs/AI_ASSISTANT.md`
- `docs/INTERVIEW.md`
- `docs/SECURITY_AUDIT.md`
- `docs/TEST_REPORT.md`
- `public/api/gemini-check.php`
- `public/assets/chat.css`
- `public/assets/chat.js`
- `public/assets/style.css`
- `public/index.php`
- `scripts/build-infinityfree.php`
- `tests/ai.php`
- `tests/ai_database.php`
- `tests/ai_http.py`
- `tests/ai_ui.cjs`
- `tests/router.php`

The business database schema is preserved. `database/upgrade_v1.sql` adds shared request counters only. `build/infinityfree/htdocs` contains the generated hosting copy, not a second editable application.
