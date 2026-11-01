# iStore — Full-Stack E-Commerce Platform with Gemini AI

A responsive, Hebrew-first (RTL) e-commerce **portfolio application** developed using PHP 8, MySQL, HTML, CSS, and vanilla JavaScript.

Features a Google Gemini-powered conversational shopping assistant that answers technology questions, provides contextual product recommendations, and compares products using real information retrieved from the store's database.

**Live Demo:** [roden-istore.duckdns.org](https://roden-istore.duckdns.org)  
**GitHub Repository:** [RudinSegas/iStore](https://github.com/RudinSegas/iStore)  
**Developer:** [Rudin Segas](https://github.com/RudinSegas)

> **Portfolio Demo:** This project is intended for educational and portfolio purposes. It does not process real payments, collect payment card information, or fulfill orders. Please use fictitious customer details and unique demo passwords.

## Features

- Responsive Hebrew RTL storefront with product search, categories, filtering, sorting, and pagination.
- User registration, authentication, session management, CSRF protection, and rate limiting.
- Guest shopping cart, stock validation, transactional demo checkout, and private order history.
- Google Gemini conversational AI assistant for technology explanations, product recommendations, and comparisons.
- Database-backed product retrieval, with server-side control over product details, pricing, availability, and links.
- Bounded conversation history, chat reset/retry functionality, and graceful handling of AI API errors and quota limits.
- Dockerized PHP/Apache and MySQL services with persistent database storage.
- Live cloud deployment with Nginx reverse proxy, HTTPS, and secure session cookies.

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.x, PDO, prepared statements |
| Database | MySQL 8.4 |
| Frontend | HTML5, CSS3, vanilla JavaScript, RTL |
| AI Integration | Google Gemini API, server-side PHP |
| Containerization | Docker, Docker Compose |
| Web Server | Apache, Nginx reverse proxy |
| Cloud Infrastructure | Google Cloud Compute Engine |
| Security | HTTPS, Let's Encrypt, CSRF protection, secure sessions |
| Version Control | Git, GitHub |

No React, npm, or Composer is required to run the application.

## Quick Start — Docker Compose

**Requirements:** Docker Engine or Docker Desktop with Docker Compose, and Git.

### 1. Clone the repository

```bash
git clone https://github.com/RudinSegas/iStore.git
cd iStore
cp .env.example .env
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

### 2. Configure environment variables

Edit `.env` and configure strong, unique values for:

- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- `APP_SECRET`

Optional Gemini AI configuration:

```dotenv
AI_ENABLED=1
GEMINI_API_KEY=<your-private-api-key>
AI_RATE_SECRET=<long-random-secret>
GEMINI_MODEL=<supported-gemini-model>
```

For local development over HTTP:

```dotenv
SESSION_SECURE_COOKIE=0
```

For production deployment over HTTPS:

```dotenv
SESSION_SECURE_COOKIE=1
```

Generate a random secret using PHP:

```bash
php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
```

Never commit `.env` or any private credentials to Git.

### 3. Start the application

```bash
docker compose up --build -d
docker compose ps
```

Open:

**http://127.0.0.1:8080**

The application binds to localhost by default. The port can be configured using `APP_PORT`.

### 4. Stop the application

```bash
docker compose down
```

**Important:** Running `docker compose down -v` removes the database volume and its stored data. Database initialization scripts execute automatically only when the database volume is first created.

## Running Without Docker

The application also supports manual PHP and MySQL installation.

1. Install PHP 8.2+ with `pdo_mysql`, `curl`, `mbstring`, sessions, and valid HTTPS CA certificates, alongside MySQL 8 or MariaDB 10.6+.
2. Create an empty `istore` database and a least-privileged database user.
3. Import the SQL files in order:
   - `database/schema.sql`
   - `database/seed.sql`
   - `database/upgrade_ai_assistant.sql`
   - `database/upgrade_v1.sql`
4. Run `php scripts/setup.php` and configure the private files `config/config.php` and, when applicable, `config/ai.php`.
5. Start the development server:

```bash
php -S 127.0.0.1:8080 -t public
```

The built-in PHP server is intended for local development only.

Always configure `public/` as the web document root, not the repository root.

Native PHP does not automatically load `.env`; configure private PHP settings or exported environment variables when running without Docker.

## Gemini AI Shopping Assistant

iStore integrates the Google Gemini API through a server-side PHP implementation.

The assistant supports:

- Natural-language conversations about computers and technology.
- Explanations of RAM, SSDs, processors, GPUs, Docker, and development workloads.
- Product recommendations based on user requirements and budgets.
- Contextual product comparisons using actual catalog data.
- Follow-up questions and conversation context.

### Architecture

The application retrieves relevant products from MySQL using server-side PHP logic.

Gemini generates conversational explanations and recommendations based on the supplied context, while PHP remains responsible for authoritative catalog information, including prices, stock availability, and product links.

The AI model does not directly execute SQL queries, modify inventory, or place orders.

API credentials remain on the server and are never intentionally exposed to browser-side JavaScript.

The storefront continues to operate when the AI service is disabled or temporarily unavailable.

**Live verification:** The deployed assistant has been manually tested with a budget-based laptop recommendation request, returning contextual explanations and comparisons of products from the store catalog.

AI availability and usage limits depend on the Google API account and applicable quotas.

See [AI Architecture](docs/AI_ASSISTANT.md) for additional implementation details.

## Cloud Deployment — Google Cloud

iStore is deployed on **Google Cloud Compute Engine**, using a Linux virtual machine running Docker Compose and Nginx.

**Live Application:** https://roden-istore.duckdns.org

### Deployment Architecture

```text
             User / Browser
                   |
                HTTPS
                   |
        Google Cloud Compute Engine
                   |
           Nginx Reverse Proxy
                   |
            127.0.0.1:8080
                   |
          Docker Compose Network
                   |
          +--------+--------+
          |                 |
      PHP 8.3            MySQL 8.4
      Apache             Database
      Container          Container
          |
     Gemini API
     (External HTTPS)
```

### Infrastructure Configuration

- Google Cloud Compute Engine virtual machine.
- Docker Compose orchestration for PHP/Apache and MySQL containers.
- Persistent MySQL database storage.
- Nginx reverse proxy forwarding requests to the locally bound application.
- DuckDNS hostname with a static public IPv4 address.
- Let's Encrypt TLS certificate managed through Certbot.
- Automatic HTTP-to-HTTPS redirection.
- Secure, HttpOnly, SameSite session cookies.
- Private environment configuration excluded from Git.
- GitHub repository for source control and version history.

### Deployment Status

**Live — Successfully deployed and manually verified.**

The application is publicly accessible through HTTPS.

Manual verification has covered:

- Storefront navigation and product browsing.
- User registration and authentication.
- Shopping cart and demo checkout.
- Order creation through the application.
- Gemini AI product recommendations.
- HTTPS connectivity and HTTP-to-HTTPS redirection.
- Secure session cookie configuration.
- Docker container availability and MySQL health.

The deployment uses a single virtual machine and is intended as a functional portfolio demonstration, not a high-availability commercial e-commerce platform.

Automated CI/CD, production monitoring, tested backup recovery, and comprehensive security testing are not claimed as completed.

## Security

The application incorporates several security measures:

- PDO prepared statements for database operations.
- Output escaping to reduce cross-site scripting risks.
- CSRF protection for sensitive application actions.
- Session management with secure cookie settings.
- Rate limiting for supported application endpoints.
- Server-side handling of Gemini API credentials.
- Private environment configuration excluded from version control.
- HTTPS encryption through Let's Encrypt.
- Automatic HTTP-to-HTTPS redirection.
- MySQL networking isolated from direct public exposure in the deployed Docker configuration.

**Security considerations:** Diagnostic endpoints, test scripts, credentials, and private configuration must not be publicly accessible. Production access to diagnostic endpoints should be blocked or protected by strong authentication.

The project has not undergone an independent security audit or penetration test.

See the project documentation:

- [Security Audit](docs/SECURITY_AUDIT.md)
- [Test Report](docs/TEST_REPORT.md)
- [Testing Instructions](tests/README.md)

## Project Structure

```text
iStore/
├── app/             Application logic and Gemini integration
├── public/          Storefront, assets, and API routes
├── config/          Private configuration and templates
├── database/        Database schema, seed, and upgrades
├── docs/            Architecture, security, and test documentation
├── scripts/         Setup and maintenance utilities
├── tests/           Test assets and instructions
├── Dockerfile       PHP/Apache container configuration
├── compose.yaml     Docker Compose services
├── .env.example     Environment configuration template
└── README.md        Project documentation
```

Historical InfinityFree deployment notes may remain in `docs/`. The active public demonstration is hosted on Google Cloud.

## Author & Acknowledgments

**Developed by Rudin Segas**

Computer Engineering graduate with interests in software development, backend systems, cloud infrastructure, and AI integration.

**GitHub:** [github.com/RudinSegas](https://github.com/RudinSegas)

This project was built using PHP, MySQL, JavaScript, Docker, Apache, Nginx, Google Cloud, Let's Encrypt, and the Google Gemini API.

Product names, brand names, and trademarks are the property of their respective owners. Third-party technologies and assets remain subject to their applicable licenses and terms.

## License & Project Scope

Copyright © 2026 Rudin Segas. All rights reserved for original project contributions.

This repository is a personal educational and portfolio project. No general license to reuse, modify, or redistribute the original project code is granted unless explicitly provided.

The application is not intended for real payment processing or commercial order fulfillment without additional security, operational, and legal review.
