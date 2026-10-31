# Security review — 2026-10-31

Source of truth: the newly uploaded `istore AI(1).zip`; previous Work implementation files were not used as the baseline.

## Credentials

The attachment included `.env` with an actual usable Gemini API key and a private `config/config.php`. Credentials were never printed. The key was used only for the explicitly requested Google API verification with synthetic questions and public demo catalog data. Rotate it in Google AI Studio; if any database password in the uploaded/private files was reused or committed, rotate that too. The final ZIP omits `.env`, private config and all original generated copies of private files. Private settings are ignored at every configuration-directory depth, including `htdocs/config/config.php`; Docker context and the release packager exclude them too.

No `.git` directory/history was supplied. Therefore this review cannot certify whether secrets were tracked or committed on GitHub. No GitHub push or history rewrite was performed. Run `python3 scripts/audit-secrets.py` in the actual checkout to inspect all reachable historical blobs without printing matches. It reports only paths and commit IDs. It is heuristic, may flag examples, and is not a substitute for credential rotation or a professional secret scanner. `.gitignore` does not untrack an already committed file. If confirmed tracked, remove it from the index with `git rm --cached -- path` after securing a private backup, rotate first, then coordinate any history cleanup with collaborators.

## Controls reviewed and strengthened

- Prepared native PDO statements; allowlisted sort/table names; escaped LIKE wildcards; no model-generated SQL.
- Server-calculated totals and fresh row locks in a transaction; per-user orders; idempotent checkout token; stock cannot be decremented by model output.
- Strict session IDs, cookie-only sessions, HttpOnly/SameSite and automatic Secure over HTTPS; ID/CSRF rotation on authentication; two-hour authenticated idle expiry.
- Shared MySQL/HMAC login, registration and contact limits; AI per-session/IP/global/day limits with atomic reservation. Authentication limits survive a new browser session. No raw IP/message values in counter tables.
- CSRF on every state-changing store POST and chat request; bounded UTF-8 scalar input; arrays rejected; safe 400/403/404/429/503 behavior without SQL traces.
- Password hashing with PHP `password_hash`/`password_verify`; eight Unicode characters minimum at registration and 72-byte maximum to avoid bcrypt truncation.
- Escaped HTML, safe image filenames, client `textContent`, local product links, self-only CSP, frame protection, no-store and nosniff.
- Server-only Google key in header; fixed Google HTTPS host, verified TLS, no redirects, model identifier validation, bounded response and deadlines; safe upstream errors only.
- Private source/config excluded from normal public document root; generated Apache deny rules also tested locally. Probe disabled by default and protected by a random separate token.

The demo collects fictitious account/contact/address information only for demonstrating flows; it has no card fields or payment provider. Do not enter real personal information into chat. Model reasoning is probabilistic; authoritative catalog fields are server-rendered but suitability advice still needs human judgment. This is a focused code review and automated test suite, not an independent penetration test or production compliance certification.
