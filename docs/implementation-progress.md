# Implementation Progress

## Completed

- Established versioned API routing at `/api/v1`.
- Implemented normalized schema for:
  - users, roles, permissions
  - buildings, room types, rooms
  - tenants, rental contracts
  - invoices, invoice items
  - payments, payment allocations
  - utility meters, utility rates, meter readings
  - expense categories, expenses
  - settings, activity logs
- Added domain models + relationships.
- Added permission middleware and applied it to protected routes.
- Added core service-layer business logic:
  - contract overlap protection
  - duplicate invoice period protection
  - payment allocation limits and invoice balance updates
  - utility consumption and charge calculation
- Implemented API controllers for core CRUD and dashboard summary.
- Seeded realistic demo data and role/permission mapping.
- Added feature tests:
  - contract conflict rejection
  - over-allocation rejection

## Not yet completed

- Full Sanctum SPA flow and token/csrf-first-party SPA hardening.
- Full frontend admin UI with Vue Router + Pinia module pages.
- PDF generation and report exports.
- Complete policy classes for per-record ownership checks.
- Advanced audit browsing UI and report pages.
- Full test matrix from specification.

## Blockers encountered

- CLI runtime tooling (`php` / `composer`) was unavailable in the current execution environment, so migrations/tests could not be executed from this session.

## Next steps

1. Install/enable PHP and Composer in PATH.
2. Run migration + seed + tests.
3. Implement Sanctum and API auth middleware updates.
4. Build frontend module pages and connect to API.
5. Add PDF/export endpoints and tests.
