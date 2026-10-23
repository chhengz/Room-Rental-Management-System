# API Overview (`/api/v1`)

## Auth

- `POST /login`
- `POST /logout`
- `GET /me`

## Dashboard

- `GET /dashboard`

## Resources

- `buildings`
- `room-types`
- `rooms`
- `tenants`
- `contracts`
- `invoices`
- `payments`
- `utility-meters`
- `meter-readings`
- `expenses`

All resources currently expose standard REST actions via `Route::apiResource`.

## Settings

- `GET /settings`
- `PUT /settings`

## Response envelope

```json
{
  "success": true,
  "message": "OK",
  "data": {}
}
```

Errors:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```
