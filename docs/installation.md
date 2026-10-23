# Installation

## Requirements

- PHP 8.3+
- Composer 2+
- Node.js 20+
- MySQL 8+

## Steps

1. `composer install`
2. `npm install`
3. `copy .env.example .env`
4. Configure MySQL in `.env`
5. `php artisan key:generate`
6. `php artisan migrate --seed`
7. `php artisan serve`
8. `npm run dev`

## Validation

- `php artisan test`
- `npm run check`
- `npm run types:check`
