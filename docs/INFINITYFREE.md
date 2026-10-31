# InfinityFree deployment and isolated connectivity test

No live hosting changes were performed. Back up files and export the existing database before a deployment you authorize yourself. Keep PHP/MySQL and frontend on the same hosting account.

## Prepare locally

1. Import only `database/upgrade_v1.sql` into the existing database; import `upgrade_ai_assistant.sql` too if its rate-limit table does not exist. Do not import seed/schema over an existing store as a reset. These upgrades only add counters and preserve all business tables.
2. The supplied `build/infinityfree/htdocs/` is generated from the tested source. After editing source, run `php scripts/build-infinityfree.php build/release-next/htdocs`; the destination must not exist. Inspect the result before upload. Existing top-level/hosted `htdocs` is never overwritten by the script.
3. Upload the **contents** of the selected generated `htdocs/` to the hosting document root when you decide to deploy. Do not upload the repository, tests, SQL, `.env` or ZIP archive. Preserve the root `.htaccess` and the `app/.htaccess` and `config/.htaccess` deny rules.
4. Create/edit private `htdocs/config/config.php` on the host with its real database hostname, port, database name, username/password, random `app_secret`, and `secure_cookies => true` on HTTPS. Do not replace your live database settings with the local sample. PHP example defaults are merged with this private array.
5. Create `htdocs/config/ai.php` privately with your **rotated** Google key, chosen model, random 32+ character `rate_secret`, and `enabled => false` until connectivity is verified. See README for the array shape. Neither the generator nor the ZIP includes either private file.
6. Confirm PHP 8.2+, cURL, mbstring and PDO MySQL. Confirm `/config/config.php`, `/config/ai.php` and `/app/bootstrap.php` return 403/404 and disclose no source. Check registration, login, cart and demo checkout with fictitious data after deployment.

The legacy flattened hosting structure is preserved by the generator. Stale duplicate snapshots from the uploaded archive are not maintained as another source implementation. Generate and upload all application files together to avoid mixed versions. Rollback means restoring your backed-up files/config; additive counter tables can remain.

## Isolated Gemini probe

`/api/gemini-check.php` does not load the database, catalog or normal application bootstrap. It is disabled and returns 404 by default. It makes one tiny actual structured-output request and reports only model, elapsed milliseconds, success or a safe error category.

Temporarily set in private `config/ai.php`:

```php
'connectivity_test_enabled' => true,
'connectivity_test_token' => 'TEMPORARY_RANDOM_SECRET_AT_LEAST_32_CHARACTERS',
```

Generate the token privately. Open your own HTTPS storefront in a browser, open its developer console and run the following, entering **only the temporary probe token**, never the Gemini API key:

```javascript
fetch('/api/gemini-check.php', {
  method: 'POST',
  headers: {'X-AI-Test-Token': prompt('Temporary probe token')},
  credentials: 'same-origin'
}).then(async r => ({status: r.status, result: await r.json()})).then(console.log);
```

The probe has a per-session one-minute cooldown; the random token is the access control, so keep it private and enable briefly. Remove the temporary token and set `connectivity_test_enabled => false` immediately afterwards. A successful probe verifies outbound HTTPS and the actual chosen model from **that account at that time**; then enable the chat and test a recommendation and follow-up there. InfinityFree's inbound browser challenge can interfere with external command-line HTTP clients; a curl error to your own site is not proof that outbound Gemini is blocked.

Error meanings: `configuration` usually means missing key/extension or upstream 401/403/404 (wrong key, model or access); `quota` means Google 429; `network` means DNS/TLS/connection failure; `timeout` means the request exceeded its deadline; `response` means invalid/truncated structured output. The response never exposes raw Google bodies or credentials. Check quota/model in AI Studio and the host's logs; do not disable TLS verification or expose phpinfo publicly.

Official/community documentation suggests outbound HTTPS can work, but cannot establish this account's connectivity. InfinityFree execution has **not** been verified for this release. If this account blocks Google HTTPS/DNS, leave AI disabled and keep the normal catalog operational. Ask hosting support to confirm outbound access to `generativelanguage.googleapis.com:443`. If unavailable, a practical future option is moving the **entire existing PHP/MySQL site together** to a PHP host permitting outbound HTTPS. No split frontend/backend host or paid service is required by this release, and no such move was performed.
