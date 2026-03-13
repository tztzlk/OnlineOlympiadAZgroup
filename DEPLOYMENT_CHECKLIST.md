# Online Olympiad Deployment Checklist

## Recommended stack
- VPS/Linux
- PHP 8.2+
- Composer 2
- Node.js 20+
- Nginx or Apache
- MySQL or MariaDB for production

## Environment
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.example`
- `VITE_API_URL=https://your-domain.example/api`
- `SESSION_DOMAIN=your-domain.example`
- `SANCTUM_STATEFUL_DOMAINS=your-domain.example`

## Backend steps
1. Install PHP dependencies with `composer install --no-dev --optimize-autoloader`
2. Create and configure `.env`
3. Run `php artisan key:generate`
4. Run `php artisan migrate --force`
5. Run `php artisan storage:link`
6. Cache config and routes:
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`

## Frontend steps
1. Install dependencies with `npm install`
2. Build assets with `npm run build`
3. Confirm `public/build/manifest.json` exists

## Operational checks
- Verify admin login works on the production domain
- Verify student registration and olympiad request flow
- Verify approved student can open and submit an olympiad
- Verify dashboard metrics reflect production traffic
- Set up database backups
- Set up queue worker or cron only if async jobs are added later

## Database note
- SQLite is acceptable for local development
- MySQL/MariaDB is recommended for production because admin analytics and concurrent writes will be more reliable
