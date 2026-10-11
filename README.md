# iStore — Full-Stack E-Commerce Application

A full-stack e-commerce web application built with **PHP, MySQL, JavaScript, HTML, and CSS**, featuring a responsive Hebrew right-to-left (RTL) user interface.

iStore provides a complete shopping experience, from browsing and searching products to managing a shopping cart, creating user accounts, and placing orders.

The project focuses on backend development, relational database design, secure user authentication, and clean application architecture.

## Tech Stack

| Layer | Technologies |
|---|---|
| Backend | PHP 8.3 |
| Database | MySQL 8.0 |
| Frontend | HTML5, CSS3, JavaScript |
| Database Access | PDO, Prepared Statements |
| Development Environment | Docker, Docker Compose |
| Version Control | Git, GitHub |

## Features

### Product Catalog
- Product listings organized into categories
- Product search by name and description
- Price sorting and category filtering
- Individual product detail pages
- Responsive product cards and layouts

### Shopping Cart
- Session-based shopping cart
- Add, update, and remove products
- Automatic total price calculation
- Product stock validation
- Cart persistence during the browsing session

### User Authentication
- User registration and login
- Secure password hashing and verification
- Session-based authentication
- Logout functionality
- Protected user-specific pages

### Order Management
- Checkout for authenticated users
- Order and order-item storage in MySQL
- Database transactions for order processing
- Automatic stock updates after successful orders
- Prevention of duplicate checkout submissions
- Personal order history

### Security
- PDO prepared statements to prevent SQL injection
- CSRF protection for state-changing requests
- Output escaping to reduce XSS risks
- Secure password storage
- Session regeneration after authentication
- Server-side input validation

### User Interface
- Hebrew RTL layout
- Responsive design for desktop and mobile
- Animated homepage hero section
- Product illustrations and category cards
- Accessible forms and navigation

## Project Structure

```text
iStore/
├── app/
│   ├── bootstrap.php
│   ├── functions.php
│   ├── actions.php
│   ├── layout.php
│   └── product-cards.php
├── config/
│   └── config.example.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── public/
│   ├── index.php
│   └── assets/
├── scripts/
├── tests/
├── docs/
├── Dockerfile
├── compose.yaml
├── .env.example
├── .gitignore
└── README.md
```

## Getting Started

### Prerequisites

- Docker Desktop
- Git

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/RudinSegas/iStore.git
cd iStore
```

**2. Configure environment variables**

Create a `.env` file from `.env.example`.

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Edit `.env` and configure the database passwords.

**3. Build and start the application**

```bash
docker compose up --build -d
```

**4. Open the application**

Visit:

http://localhost:8080

The database schema and sample products are initialized automatically when the database is created for the first time.

## Database Design

The application uses a relational MySQL database containing six main tables:

| Table | Purpose |
|---|---|
| `users` | User accounts and authentication |
| `categories` | Product categories |
| `products` | Product details, prices, and inventory |
| `orders` | Customer orders |
| `order_items` | Products associated with each order |
| `contact_requests` | Contact form submissions |

### Order Processing

The checkout process uses database transactions to maintain data consistency.

When an order is submitted, the application:

1. Validates the user's session and shopping cart.
2. Retrieves current product prices and stock levels.
3. Locks the relevant inventory rows.
4. Creates the order and its associated items.
5. Updates product stock.
6. Commits the transaction if all operations succeed.

If an operation fails, the transaction is rolled back to prevent partial orders or inconsistent inventory.

## Testing

The project includes PHP checks and HTTP integration tests.

Run the PHP checks:

```bash
php scripts/check.php
```

Run the HTTP tests against a local instance:

```bash
python tests/smoke.py http://localhost:8080
```

The integration tests modify database data, so they should be executed against a disposable development database.

Manual testing includes registration, login, product search, cart management, checkout, and order history.

## Technical Highlights

**Backend Development:** Server-rendered PHP application with reusable functions, request handling, session management, and database integration.

**Database Design:** Relational schema with foreign keys, indexes, and transactional order processing.

**Security:** Prepared SQL statements, password hashing, CSRF protection, input validation, and output escaping.

**Data Consistency:** Inventory validation and transactional checkout to maintain consistent stock and order records.

**Containerization:** Docker-based development environment for running PHP and MySQL consistently across different machines.

## Future Improvements

- Admin dashboard for product and inventory management
- Email verification and password recovery
- Product pagination
- Automated deployment pipeline
- Expanded automated testing
- Improved monitoring and logging

## Project Scope

iStore is a functional portfolio application developed for educational and demonstration purposes.

Products and prices are sample data. Checkout records orders in the database but does not process real payments or arrange shipments.

## Author

**Roden Segas**

B.Sc. Computer Engineering


GitHub: https://github.com/RudinSegas