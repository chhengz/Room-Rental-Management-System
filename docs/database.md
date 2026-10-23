# Database Domain Summary

## Access and security

- `users`
- `roles`
- `permissions`
- `role_user`
- `permission_user`
- `permission_role`

## Property and occupancy

- `buildings`
- `room_types`
- `rooms`
- `tenants`
- `rental_contracts`

## Billing and collections

- `invoices`
- `invoice_items`
- `payments`
- `payment_allocations`

## Utilities

- `utility_meters`
- `utility_rates`
- `meter_readings`

## Expenses and system

- `expense_categories`
- `expenses`
- `settings`
- `activity_logs`

## Core integrity constraints

- Unique room number per building.
- Unique invoice/payment/contract identifiers.
- Invoice and payment monetary fields use decimal types.
- Payment allocation total cannot exceed payment amount (service-level rule).
- Overlapping active/upcoming contracts for same room are rejected (service-level rule).
