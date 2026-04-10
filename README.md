# Library API (Laravel 13)

Production-ready REST API for managing a large library dataset with versioning, authentication, and MySQL-backed scaling.

## Features

- Laravel 13 API project with Sanctum token authentication
- API versioning (`/api/v1`, `/api/v2`)
- Full REST resources for:
  - Authors
  - Categories
  - Books
  - Members
  - Loans
- Secure auth endpoints (`register`, `login`, `me`, `logout`)
- Role and permission system (Spatie Permission)
- Rate limiting on public and protected routes
- Query filtering + pagination for large data volumes
- Dedicated search endpoints (`search/global`, `search/books`, `search/members`)
- Transaction-safe book borrowing and return workflow
- High-volume seeders for realistic MySQL dataset generation

## Tech

- PHP 8.3+
- Laravel 13
- MySQL 8+
- Laravel Sanctum

## Quick Start

1. Configure environment:

```bash
cp .env.example .env
```

2. Update `.env` database values:

- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=library_api`
- `DB_USERNAME=root`
- `DB_PASSWORD=...`

3. Create database in MySQL:

```sql
CREATE DATABASE library_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Run migrations and seeders:

```bash
php artisan migrate:fresh --seed
```

5. Start server:

```bash
php artisan serve
```

API root will be available at `http://127.0.0.1:8000/api`.

## Authentication

Use Sanctum Bearer tokens:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `GET /api/v1/auth/me` (auth required)
- `POST /api/v1/auth/logout` (auth required)

Add header to protected requests:

```http
Authorization: Bearer <token>
Accept: application/json
```

## Main Endpoints (v1)

- `GET|POST /api/v1/authors`
- `GET|PUT|PATCH|DELETE /api/v1/authors/{id}`
- `GET|POST /api/v1/categories`
- `GET|PUT|PATCH|DELETE /api/v1/categories/{id}`
- `GET|POST /api/v1/books`
- `GET|PUT|PATCH|DELETE /api/v1/books/{id}`
- `GET|POST /api/v1/members`
- `GET|PUT|PATCH|DELETE /api/v1/members/{id}`
- `GET|POST /api/v1/loans`
- `GET|PUT|PATCH|DELETE /api/v1/loans/{id}`
- `PATCH /api/v1/loans/{id}/return`
- `GET /api/v1/search/global?q=...`
- `GET /api/v1/search/books?q=...`
- `GET /api/v1/search/members?q=...`

Health/status:

- `GET /api/status`
- `GET /api/v2/status`

## Filtering and Pagination

Most list endpoints support:

- `per_page` (max 100)
- `search`

Additional examples:

- `GET /api/v1/books?search=clean+code&author_id=10&category_id=2&sort_by=published_year&sort_direction=desc&per_page=50`
- `GET /api/v1/members?is_active=true&search=MEM-`
- `GET /api/v1/loans?status=overdue&member_id=45`

## Security Notes

- Strong password policy on registration
- Token expiration enabled through `SANCTUM_EXPIRATION`
- Public auth routes are rate-limited
- Protected routes are token-authenticated and rate-limited
- Deletion safeguards for linked or active records
- Loan creation and return use DB transactions + row locking
- Endpoint-level authorization with role/permission middleware

## Roles and Permissions

Default seeded roles:

- `admin`: full access
- `librarian`: operational CRUD (no hard deletes by default)
- `member`: read/search focused access

Users registered through `/api/v1/auth/register` are assigned the `member` role automatically.

## Seeded Admin User

Seed process creates:

- Email: `admin@library.local`
- Password: `ChangeMe@12345`

Change this password immediately in non-local environments.

## Large Data Seeding

Default seeding generates a large dataset suitable for load/performance testing:

- 45 categories
- 1,800 authors
- 15,000 books
- 8,000 members
- 25,000 loans

If you need smaller data for local development, adjust counts in `database/seeders/LibrarySeeder.php`.
