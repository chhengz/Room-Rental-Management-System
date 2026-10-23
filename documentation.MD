# MASTER AI DEVELOPMENT PROMPT
## Build a Complete Room Rental Management System with Laravel + Vue.js

### 1. Your role

Act as a senior full-stack software engineer, Laravel architect, Vue.js developer, database designer, UI/UX designer, security engineer, and QA engineer.

Build a complete, working Room Rental Management System for a room rental business. The project is inspired by the rental management concept at https://rental.roombox.site/ but must use its own implementation, code, database, and visual design.

Do not produce only a proposal, tutorial, mockup, or code samples. Implement the actual application in the project workspace.

### 2. Technology stack

Use the following stack:

- Backend: Laravel with a stable, mutually compatible release of PHP.
- Frontend: Vue.js 3 with Composition API and TypeScript.
- Build tool: Vite.
- Database: MySQL.
- API: Laravel REST API returning consistent JSON responses.
- Authentication: Laravel Sanctum using secure first-party SPA authentication where appropriate.
- Frontend routing: Vue Router.
- State management: Pinia where shared state is needed.
- HTTP client: Axios.
- Styling: Tailwind CSS and reusable Vue components.
- Charts: Chart.js or another compatible chart library.
- PDF generation: a Laravel-compatible PDF library.
- Testing: Pest or PHPUnit for backend tests and Vitest for frontend tests.
- Source control: Git.

Before installation, inspect the environment and choose compatible stable versions. Do not mix incompatible major versions or copy obsolete configuration from older tutorials.

Prefer a same-origin deployment for the SPA and API when practical.

### 3. Development rules

1. Inspect the existing workspace before creating or overwriting files.
2. If the project already exists, preserve working code and extend it safely.
3. Create a clear project structure and document important architecture decisions.
4. Implement features in phases, in the order specified below.
5. Finish and test each phase before moving to the next.
6. Do not replace real backend logic with hard-coded frontend data.
7. Do not leave important buttons, forms, filters, or navigation links nonfunctional.
8. Do not silently omit required features. Document any blocker explicitly.
9. Use database migrations, factories, and seeders.
10. Use Form Requests or equivalent centralized validation, policies, and services where appropriate.
11. Use database transactions for operations involving multiple financial records.
12. Avoid duplicated business logic across controllers.
13. Use pagination for large datasets and eager loading for related records.
14. Keep configuration and secrets in environment variables.
15. Do not commit real credentials or production secrets.
16. Use clear naming, consistent formatting, and maintainable code.
17. Provide commands to install, migrate, seed, test, and run the project.
18. After each phase, summarize files created or modified, commands executed, test results, known limitations, and the next phase.
19. Never claim a test passed unless it was actually executed and passed.
20. Do not stop after generating a file tree or writing the initial scaffold. Continue implementation until all required phases are complete, subject to available tools and execution limits.

### 4. Required application modules

#### A. Authentication and authorization

Implement:
- Login and logout.
- Authenticated user profile.
- Password change and secure password storage.
- Session expiration and unauthorized-request handling.
- Staff account management.
- Role and permission management.
- Backend route authorization and model policies.
- Permission-aware frontend navigation.
- Account activation and deactivation.
- Audit logging for sensitive actions.

Required roles include Super Administrator, Owner/Manager, Accountant, and Rental Staff. Permissions must be configurable and enforced on the server.

#### B. Dashboard

Create a responsive dashboard showing:
- Total rentable rooms.
- Occupied, available, reserved, and maintenance rooms.
- Occupancy rate.
- Current-period invoices and collected payments.
- Outstanding and overdue balances.
- Current-period expenses and net operating result.
- Utility consumption summaries.
- Recent payments, invoices, registrations, and activity.
- Upcoming contract expirations.
- Revenue and expense charts.
- Filters for date range and building when applicable.

All figures must come from real database queries and documented business definitions.

#### C. Staff management

Implement:
- Staff list with search, filtering, sorting, and pagination.
- Create and edit staff profiles.
- Assign roles and permissions.
- Activate or deactivate accounts.
- Prevent unauthorized privilege escalation.
- Validate unique email addresses.
- Record relevant account changes in audit logs.

#### D. Building and room management

Implement:
- Buildings and room types.
- Room number, floor, capacity, description, rental rate, and images.
- Room create, view, update, and authorized status changes.
- Search and filters by status, building, floor, type, and price.
- Room detail page with current tenant and contract information.
- Room availability overview.
- Maintenance and inactive room handling.
- Prevention of conflicting active rental contracts.

A room status must remain consistent with contracts and reservations. Do not let arbitrary status changes produce contradictory records.

#### E. Tenant management

Implement:
- Tenant registration and editing.
- Contact information and optional identification fields.
- Tenant status.
- Rental history.
- Current room and contract.
- Invoice and payment history.
- Outstanding balances.
- Move-in, move-out, and contract termination records.

Collect only personal data needed by the business and restrict access appropriately.

#### F. Rental contracts

Implement:
- Contract creation and editing subject to business rules.
- Tenant and room assignment.
- Start date and end date.
- Rent amount, deposit, billing frequency, due day, and terms.
- Upcoming, active, expired, renewed, and terminated states.
- Contract history.
- Renewal workflow.
- Prevention of overlapping active contracts for the same room.
- Automatic or scheduled contract-expiry reminders when scheduling infrastructure is configured.

Define how deposits, prorated rent, early termination, and refunds are handled. Do not invent undocumented business rules; make uncertain policies configurable or document a proposed default.

#### G. Invoice and billing management

Implement:
- Invoice listing, creation, details, editing where allowed, and printing.
- Unique invoice numbers.
- Rent, utilities, deposits, late fees, discounts, and additional service line items where applicable.
- Issue date, due date, and billing period.
- Draft, issued, partially paid, paid, overdue, and void states.
- Payment recording and invoice allocation.
- Cash, bank transfer, and configurable payment methods.
- Receipts and PDF invoices.
- Outstanding balance calculations.
- Overdue filters and collection reports.
- Duplicate billing prevention.
- Credit notes or controlled adjustment workflows.

Use decimal database columns for money. Calculate totals and balances on the server. Use database transactions and appropriate locking or uniqueness constraints to prevent duplicate payments and billing periods.

Do not delete or silently rewrite finalized financial records. Preserve a traceable correction history.

#### H. Utilities management

Implement:
- Electricity and water utility types.
- Meter registration and meter history.
- Previous and current readings.
- Reading dates and recorded-by user.
- Configurable unit rates and optional fixed charges.
- Consumption calculations.
- Utility charge generation.
- Manual adjustments with required reasons.
- Reading validation and anomaly warnings.
- Historical rates stored against finalized charges.
- Utility invoice line items and consumption reports.

Reject invalid negative consumption unless an explicitly documented meter rollover or replacement procedure is used. Define how shared meters and billing-period changes are handled.

#### I. Expense management

Implement:
- Expense categories.
- Expense creation, editing, and authorized cancellation.
- Amount, date, description, payment method, and reference.
- Optional receipt attachments with file validation.
- Search and date/category filters.
- Expense totals by period and category.
- Expense reports.

Prevent expenses from being counted twice and keep audit history for corrections.

#### J. Reporting and exports

Implement reports for:
- Income and payments collected.
- Invoices issued and outstanding balances.
- Overdue rent.
- Expenses by category.
- Net operating result.
- Room occupancy and availability.
- Tenant and contract activity.
- Electricity and water consumption.
- Utility charges.
- Payment method summaries.

Provide date filters, pagination where appropriate, CSV export, and printable PDF reports.

Clearly distinguish invoice revenue, cash collected, outstanding receivables, and operating expenses. Do not label invoice totals as cash received.

#### K. Activity logs and settings

Implement:
- Audit logs for sensitive operations.
- Actor, action, timestamp, target record, and appropriate before/after details.
- Read-only audit history for normal users.
- Business name, address, currency, timezone, invoice prefix, billing settings, utility rates, and other configurable options.
- Safe defaults and validation for configuration changes.

### 5. Database design

Create normalized migrations, models, relationships, factories, and seeders for at least the following entities:

- users
- roles and permissions
- buildings
- room_types
- rooms
- tenants
- rental_contracts
- invoices
- invoice_items
- payments
- payment_allocations, if needed for multiple invoices and partial allocations
- utility_meters
- meter_readings
- utility_rates or effective-dated rate records
- expenses
- expense_categories
- activity_logs
- settings

Add timestamps, foreign keys, appropriate indexes, uniqueness constraints, and deletion rules.

Use decimal columns for monetary amounts. Use transactions and constraints to preserve data integrity. Keep historical rental, invoice, payment, meter, and rate information.

Create a documented entity relationship diagram or equivalent relationship documentation.

### 6. Backend architecture

Use a maintainable Laravel structure, including:

- Models and relationships.
- API controllers.
- Form Request validation.
- Policies and middleware.
- Service classes for billing, payments, utilities, and reporting where appropriate.
- Resource classes for API responses.
- Database transactions for financial workflows.
- Centralized exception handling.
- Consistent HTTP status codes.
- API pagination and filtering.
- Automated tests.

Use RESTful routes under a versioned prefix such as `/api/v1`.

Document all endpoints and provide example requests and responses.

Never rely on frontend-only authorization. Validate permissions and ownership on every protected backend operation.

### 7. Frontend architecture

Use Vue 3, TypeScript, Vue Router, Pinia where appropriate, Axios, and reusable components.

Create a professional admin interface with:
- Sidebar navigation.
- Header with user menu and notifications area.
- Breadcrumbs.
- Dashboard cards and charts.
- Data tables with search, sorting, filtering, and pagination.
- Validated forms.
- Confirmation dialogs.
- Loading, empty, success, and error states.
- Toast notifications.
- Responsive layouts.
- Permission-aware navigation.
- Reusable badges for room, invoice, contract, and payment statuses.

Create a centralized API client with consistent handling of validation errors, authentication failures, and server errors.

Use a coherent visual design inspired by modern property management software. Do not copy proprietary assets from the reference website.

### 8. Suggested project organization

Keep the repository easy to install and maintain. A single repository with separate backend and frontend directories is acceptable.

room-rental-management/
  backend/
    app/
      Http/
        Controllers/Api/V1/
        Requests/
        Resources/
      Models/
      Policies/
      Services/
    database/
      factories/
      migrations/
      seeders/
    routes/
    tests/
    .env.example
    composer.json
  frontend/
    src/
      api/
      assets/
      components/
      layouts/
      router/
      stores/
      types/
      utils/
      views/
    tests/
    package.json
    vite.config.ts
  docs/
    architecture.md
    database.md
    api.md
    installation.md
    user-guide.md
    test-plan.md
  README.md
  .gitignore

Adjust the structure if the chosen Laravel starter kit or integrated deployment architecture requires it. Do not duplicate application logic unnecessarily.

### 9. Implementation phases

Phase 1: Environment verification, project scaffolding, architecture, database configuration, shared layout, and API connectivity.

Phase 2: Authentication, staff accounts, roles, permissions, and security tests.

Phase 3: Buildings, rooms, tenants, and rental contracts.

Phase 4: Invoices, payment allocation, receipts, balances, and PDF generation.

Phase 5: Utility meters, readings, rate calculations, and expense management.

Phase 6: Database-backed dashboard, reports, charts, and exports.

Phase 7: Integration testing, security review, performance improvements, deployment configuration, documentation, and final acceptance.

At the end of each phase, run relevant tests and fix failures before proceeding.

### 10. Mandatory business rules

- One room cannot have conflicting active contracts.
- A tenant cannot be billed twice for the same intended billing period unless an explicitly allowed additional charge exists.
- Payment allocation cannot exceed the valid available payment amount.
- Outstanding balances must be calculated consistently from finalized invoices, allocated payments, and applicable credits.
- Voided invoices and reversed payments must be handled consistently in reports.
- Historical utility charges must retain the rates and readings used when they were generated.
- Unauthorized users cannot read or modify restricted financial or personal data.
- Finalized transactions must have traceable correction procedures.
- Dashboard metrics and reports must reconcile to the underlying records.
- Use a documented currency and rounding policy.
- Enforce important rules in backend logic and database constraints where possible.

### 11. Testing requirements

Create backend tests for:
- Login and authorization.
- Staff role restrictions.
- Room CRUD and filtering.
- Conflicting rental contracts.
- Invoice calculations and duplicate prevention.
- Full and partial payments.
- Invalid payment allocations.
- Overdue invoice status.
- Utility reading and charge calculations.
- Expense reporting.
- Dashboard totals and date filters.
- PDF/export endpoints and validation.
- Audit logging.
- Cross-user access restrictions.

Create frontend tests for:
- Form validation.
- API error handling.
- Protected routes.
- Permission-aware navigation.
- Key financial forms and calculations displayed from server responses.

Run tests and report actual results. Fix failures before declaring the project complete.

### 12. Seed data and demo accounts

Create realistic development-only seed data for:
- Buildings and rooms.
- Tenants and rental contracts.
- Invoices and payment records.
- Utility readings and rates.
- Expense categories and sample expenses.
- Staff accounts and roles.

Document demo credentials only for local development. Do not seed production with publicly known passwords or unrestricted administrator accounts.

### 13. Deployment and documentation

Provide:
- Environment requirements and compatible version information.
- Installation commands for backend and frontend.
- MySQL database setup.
- Environment variable configuration.
- Migration and seeding commands.
- Development startup commands.
- Production build commands.
- Web server and HTTPS configuration guidance.
- Scheduled-task setup where needed.
- Storage permissions and safe file handling.
- Backup and restore instructions.
- API documentation.
- User guide.
- Test commands and final acceptance checklist.

Never commit real `.env` files, API secrets, database passwords, or production credentials.

### 14. Final acceptance criteria

The project is complete only when:
- All required modules are implemented.
- Frontend pages use the actual backend API.
- Database migrations run successfully.
- Authentication and permissions are enforced.
- Core financial and utility calculations are tested.
- Invoice and report exports work.
- Critical workflows have automated test coverage.
- The frontend production build succeeds.
- The backend tests pass, or any remaining failures are explicitly documented.
- Setup instructions work from a clean environment.
- No critical buttons or routes are left as placeholders.
- Known limitations and future enhancements are documented.

### 15. First action

Begin by inspecting the current workspace and available runtime versions.

Then:
1. Report the detected environment and any compatibility issues.
2. Propose the final compatible package versions and architecture.
3. Create a phased implementation checklist.
4. Implement Phase 1 in the actual project files.
5. Run the available checks and tests.
6. Report the completed work and proceed to Phase 2.

Continue phase by phase until the entire system is implemented and verified. If a single execution session cannot finish everything, maintain a progress document such as `docs/implementation-progress.md` with completed tasks, current files, test results, blockers, and exact next steps. Resume from that document without rebuilding completed work.
