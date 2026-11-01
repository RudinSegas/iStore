# iStore — PHP E-Commerce Portfolio with Gemini AI

A responsive, Hebrew-first (RTL) e-commerce **portfolio demo** built with PHP, MySQL, HTML, CSS and vanilla JavaScript. Its Gemini-powered shopping assistant can answer general technology questions and help shoppers compare products using information retrieved from the store's database.

**Repository:** https://github.com/RudinSegas/iStore  
**Current source revision documented:** `8db5436` (Gemini AI assistant and platform improvements).

> **Demo only:** No real payment processing, card collection, or order fulfillment. Use fictitious customer details and unique demo passwords.

## Features

- Responsive Hebrew RTL storefront with product search, categories, filtering, sorting and pagination.
- User registration and sign-in, session management, CSRF protection and rate limits.
- Guest cart, stock validation, transactional demo checkout, order token protection and private order history.
- Gemini conversational assistant for technology explanations and contextual product recommendations.
- Database-backed product candidates; PHP controls catalog facts, prices, availability and product links. Gemini does not execute SQL or place orders.
- Bounded chat history, reset/retry experience, and graceful handling of AI outages or quotas.
- Docker Compose development setup using PHP 8.3 + Apache and MySQL 8.4.

## Technology

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.x, PDO prepared statements |
| Database | MySQL 8.x (MariaDB also supported for manual setup) |
| Frontend | HTML, CSS, vanilla JavaScript, RTL |
| AI | Google Gemini API, server-side integration |
| Local runtime | Docker Compose, Apache |
| Cloud deployment target | Google Cloud Compute Engine, Docker Compose, Nginx reverse proxy |

No React, npm or Composer is required to run the application.

## Quick start — Docker Compose (recommended)

**Requirements:** Docker Engine or Docker Desktop with the Compose plugin, and Git.

```bash
git clone https://github.com/RudinSegas/iStore.git
cd iStore
cp .env.example .env
```

On Windows PowerShell, replace `cp` with `Copy-Item .env.example .env`.

Edit `.env` locally and set **unique, strong values** for at least:

- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- `APP_SECRET`

Optional Gemini settings:

- `AI_ENABLED=1`
- `GEMINI_API_KEY=<your private API key>`
- `AI_RATE_SECRET=<long random secret>`
- `GEMINI_MODEL=<model available to your Google AI account>`

Generate a random 64-character hexadecimal secret with PHP if installed:

```bash
php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
```

Then start the containers:

```bash
docker compose up --build -d
docker compose ps
```

Open **http://127.0.0.1:8080** (or the port configured through `APP_PORT`). The web container binds to localhost by default.

Stop without deleting data:

```bash
docker compose down
```

**Important:** `docker compose down -v` deletes the database volume. The SQL initialization scripts run automatically **only when the database volume is first created**. Do not re-seed a database containing real or existing demo data.

## Running without Docker

1. Install PHP 8.2+ with `pdo_mysql`, `curl`, `mbstring`, sessions and valid HTTPS CA certificates, plus MySQL 8 / MariaDB 10.6+.
2. Create an empty `istore` database and a least-privileged application database user.
3. Import these files **in order** into the empty database:
   - `database/schema.sql`
   - `database/seed.sql`
   - `database/upgrade_ai_assistant.sql`
   - `database/upgrade_v1.sql`
4. Run `php scripts/setup.php` to create private configuration files; edit `config/config.php` for your local database and `config/ai.php` if using Gemini.
5. Run `php -S 127.0.0.1:8080 -t public` and open http://127.0.0.1:8080.

The built-in PHP server is **for local development only**. Always serve `public/` as the document root, never the repository root. Native PHP does not automatically load `.env`; use the private PHP configuration or exported environment variables for this mode.

## Gemini AI assistant

The assistant sends user requests to the Gemini API **from PHP on the server**, not from browser JavaScript. The server retrieves and filters relevant product candidates and resolves catalog facts, while Gemini generates conversational explanations and comparisons. General technology questions can be answered without inventing store inventory.

The storefront works with AI disabled. To enable it in Docker, set the relevant environment variables in your private `.env` file and restart the web container. For manual PHP hosting, configure private `config/ai.php` instead.

Keep `GEMINI_API_KEY`, database credentials, `APP_SECRET`, and `AI_RATE_SECRET` **out of Git and browser-accessible files**. Rotate any previously exposed API key. The application has its own rate limits, which do not guarantee Google API availability or a free billing tier.

See [AI architecture](docs/AI_ASSISTANT.md) for the design and limitations.

## Deployment — Google Cloud Compute Engine

The intended deployment is a **single VM** running:

```text
Browser (HTTPS)
      |
  Nginx on VM
      |
127.0.0.1:8080
      |
Apache + PHP container
      |
 MySQL container (private Docker network)
```

High-level deployment checklist:

1. Provision a Linux VM with Docker Engine, Docker Compose, Nginx and suitable RAM/storage.
2. Clone this repository on the VM.
3. Create a **new, private** `.env` file on the VM using `.env.example` as a starting point. Never commit or share its values.
4. Run `docker compose up --build -d` and inspect container health and logs.
5. Configure Nginx to reverse-proxy to `http://127.0.0.1:8080`, restrict access to diagnostics, and add HTTPS/TLS for a real hostname.
6. Test login, catalog, checkout, Gemini behavior, error handling and database persistence before announcing the deployment as live.

**Status:** The updated source has been pushed to GitHub. The Google Cloud deployment and public HTTPS verification are **not yet completed**. This section describes the target setup, not a tested production installation.

Do not publish MySQL's port, `.env`, private config, test tokens, or debug endpoints. Source files such as `gemini-test.php` and `public/api/gemini-check.php` may remain in Git for portfolio purposes, but production access must be blocked or the probes kept disabled and strongly authenticated. Do not run the root-level test script as a public web endpoint.

## Security and testing

- PDO prepared statements, output escaping, CSRF protections and session controls are part of the application design.
- Diagnostics and test source can be reviewed in the repository; they are **not** public production APIs.
- See [security audit](docs/SECURITY_AUDIT.md), [test report](docs/TEST_REPORT.md) and [test instructions](tests/README.md).
- Documentation and static review are not substitutes for testing the actual deployed system.

## Project structure

```text
app/          Application logic and Gemini integration
public/       Public web document root, assets and API routes
config/       Private runtime configuration and example templates
database/     Schema, seed and incremental SQL upgrades
docs/         Architecture, security and testing documentation
scripts/      Setup, audit and packaging helpers
tests/        Test assets and instructions
Dockerfile    PHP/Apache container image
compose.yaml  PHP + MySQL development services
.env.example  Environment variable template (no live secrets)
```

Older InfinityFree notes may remain under `docs/` for historical reference. The former generated `build/` deployment directory is not part of the current source checkout and should not be treated as an active deployment.

## License and scope

This repository is a learning and portfolio project. No license grant is implied unless a license file is included in the repository. Do not use it as a payment-processing or production commerce system without further security, operations and legal review.
