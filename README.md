# Rental Management System

Laravel + Vue room rental management backend foundation, implemented from the project specification in [documentation.MD](./documentation.MD).

## Implemented scope

- Versioned REST API under `/api/v1`.
- Core modules scaffolded and wired:
  - Auth (session-based login/logout/me)
  - Roles and permissions (database + middleware enforcement)
  - Buildings, room types, rooms
  - Tenants and rental contracts
  - Invoices, invoice items, payments, payment allocations
  - Utility meters, rates, and readings
  - Expense categories and expenses
  - Settings and activity logs
  - Dashboard summary endpoint
- Business-rule services:
  - Contract conflict prevention
  - Duplicate invoice period prevention
  - Payment allocation guardrails
  - Utility charge calculation
- Demo seed data across all main modules.
- Initial feature tests for critical financial/contract constraints.

## Tech notes

- Backend: Laravel 13 + PHP 8.3
- Frontend scaffold: Vue 3 + TypeScript + Vite (starter)
- Database: migrations are MySQL-friendly and also run on SQLite for local tests

## Local setup

1. Install PHP 8.3+, Composer, Node.js 20+, and MySQL 8+.
2. Copy env:
   - `copy .env.example .env`
3. Configure database in `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=rental_management`
   - `DB_USERNAME=...`
   - `DB_PASSWORD=...`
4. Install dependencies:
   - `composer install`
   - `npm install`
5. Generate key + migrate + seed:
   - `php artisan key:generate`
   - `php artisan migrate --seed`
6. Run app:
   - `php artisan serve`
   - `npm run dev`

## Demo credentials (local dev only)

- `admin@rental.local` / `password`
- `manager@rental.local` / `password`

## Validation & test commands

- `php artisan test`
- `npm run check`
- `npm run types:check`

## Current status

The backend domain and API baseline are implemented. Frontend module pages, Sanctum SPA auth flow, PDF generation, CSV/PDF exports, and complete test coverage are the next phases.
