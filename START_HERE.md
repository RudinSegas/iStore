# iStore — Quick Start Guide

**Full-Stack E-Commerce Platform with Google Gemini AI**

iStore is a responsive, Hebrew-first (RTL) e-commerce portfolio application built with PHP 8, MySQL, HTML, CSS, and vanilla JavaScript.

The application includes user authentication, product browsing, shopping carts, demo checkout, and a conversational AI shopping assistant powered by Google Gemini.

**Live Demo:** [roden-istore.duckdns.org](https://roden-istore.duckdns.org)  
**GitHub:** [RudinSegas/iStore](https://github.com/RudinSegas/iStore)  
**Developer:** [Rudin Segas](https://github.com/RudinSegas)

## 1. Requirements

- Git
- Docker Engine or Docker Desktop
- Docker Compose

No React, npm, or Composer installation is required.

## 2. Clone the Repository

```bash
git clone https://github.com/RudinSegas/iStore.git
cd iStore
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

## 3. Configure Environment Variables

Open `.env` and configure unique, strong values for:

- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- `APP_SECRET`

To enable the Gemini AI assistant, configure:

```dotenv
AI_ENABLED=1
GEMINI_API_KEY=<your-private-api-key>
AI_RATE_SECRET=<long-random-secret>
GEMINI_MODEL=<supported-gemini-model>
```

Use a Gemini model available to your Google API account.

For local development over HTTP:

```dotenv
SESSION_SECURE_COOKIE=0
```

For HTTPS deployment:

```dotenv
SESSION_SECURE_COOKIE=1
```

The storefront also works with `AI_ENABLED=0`.

**Security:** Never commit `.env`, database passwords, API keys, or other private credentials to GitHub.

## 4. Start the Application

```bash
docker compose up --build -d
docker compose ps
```

Open the local application:

**http://127.0.0.1:8080**

Docker Compose starts the PHP/Apache and MySQL services. The database is initialized automatically when its data volume is first created.

## 5. Stop the Application

```bash
docker compose down
```

This stops the containers without deleting persistent database data.

**Warning:** `docker compose down -v` also removes the database volume and its stored data.

## 6. Live Cloud Deployment

**Status: Live and manually verified**

The application is deployed on Google Cloud Compute Engine.

**Live URL:** https://roden-istore.duckdns.org

### Infrastructure

- **Google Cloud Compute Engine** — Linux virtual machine
- **Docker Compose** — PHP/Apache and MySQL containers
- **Nginx** — Reverse proxy
- **Let's Encrypt** — HTTPS/TLS certificate
- **DuckDNS** — Public hostname
- **Google Gemini API** — Server-side AI integration
- **GitHub** — Source control

The deployment uses HTTPS, automatic HTTP-to-HTTPS redirection, and secure session cookies.

Storefront navigation, authentication, shopping cart, demo checkout, and Gemini product recommendations have been manually tested.

This is a portfolio deployment, not a production-ready commercial payment platform.

## 7. Additional Documentation

For architecture, manual installation, security details, and testing instructions, see:

- [README — Full Project Documentation](README.md)
- [Gemini AI Architecture](docs/AI_ASSISTANT.md)
- [Security Audit](docs/SECURITY_AUDIT.md)
- [Test Report](docs/TEST_REPORT.md)
- [Testing Instructions](tests/README.md)

## Author

**Developed by Rudin Segas**  
[GitHub Profile](https://github.com/RudinSegas)

Copyright © 2026 Rudin Segas. All rights reserved for original project contributions.

> **Portfolio Demo:** No real payment processing or order fulfillment. Use fictitious customer details and unique demo passwords.
