# MVP verification report

Executed on 11 October 2026 against PHP 8.3.6 and MariaDB 10.11.14 with the supplied schema and sample seed. The isolated test database used local TCP and disabled database grants for the test harness only; this setting is not present in the deliverable's Docker or native setup configuration.

## Passed

- 31 HTTP integration assertions in `tests/smoke.py`.
- All seven PHP application/config files passed `php -l` syntax checking; `scripts/check.php` also executed successfully.
- Seven independent helper assertions: HTML escaping, integer money formatting, quantity bounds and invalid quantity rejection.
- JavaScript parsed successfully with `node --check`.
- All five bundled SVG illustrations parsed as valid XML.

## Integration coverage

Homepage RTL and four categories; twelve product cards; text search; category filtering; empty results; CSRF rejection; guest cart; quantity changes and total recalculation; invalid quantities; stock limits; anonymous checkout gate; registration and automatic login; database-backed checkout; client-submitted price ignored; duplicate submission rejection; cart clearing; logout; incorrect-password rejection; successful login; order persistence; duplicate-email rejection; cart item removal; contact submission; escaped search text; login requirement for order history; second-user registration; order isolation between users; unknown-product rejection; unknown-action rejection; unknown-route 404.

## Database observations after the test

| Record | Observed value |
| --- | --- |
| Order total | 599800 agorot (₪5,998.00) |
| Order item | product 1, quantity 2, unit price 299900 agorot |
| Product 1 stock | 13, reduced from seeded 15 |
| Contact requests | 1 saved row |

The duplicate checkout did not create a second order. Order history remained available after logout/login and was absent in an independently registered user's session.

## Not executed

- Interactive browser QA, mobile screenshots or visual accessibility audits. Responsive CSS breakpoints and accessible markup are implemented, but device appearance needs manual review.
- Docker Compose execution: no Docker daemon was available. The app was exercised using native PHP and MariaDB instead; the supplied Docker path is unverified here.
- MySQL 8 execution specifically: SQL was validated on compatible MariaDB 10.11.
- Concurrent checkout load testing, forced database-disconnect recovery or automated transactional rollback fault injection.
- Production deployment, payment processing, email delivery or GitHub publication.

The original public URL was unreachable from the build environment. Visual reconstruction uses the supplied screenshot's branding, blue/turquoise background, dark header, category names and contact section, with deliberate changes to improve responsiveness and spacing.
