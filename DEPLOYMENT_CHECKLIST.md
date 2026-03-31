# Online Olympiad Deployment Checklist

## Infrastructure

- `1 VPS` with Ubuntu 22.04/24.04
- `4 vCPU / 8 GB RAM / NVMe SSD` for first launch
- Domain `A` record points to the server IP
- SSL enabled with Let's Encrypt
- Laravel Forge site created with document root in `public/`

## Required Services

- PHP 8.2+
- Nginx
- MySQL 8
- Redis
- Supervisor or Forge queue workers
- Scheduler enabled in Forge or cron

## Production Environment

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.example`
- `FRONTEND_URL=https://your-domain.example`
- `DB_CONNECTION=mysql`
- `SESSION_DRIVER=redis`
- `CACHE_STORE=redis`
- `QUEUE_CONNECTION=redis`
- `REDIS_HOST=127.0.0.1`
- `SESSION_SECURE_COOKIE=true`
- `SECURITY_ENFORCE_HTTPS=true`
- `CORS_ALLOWED_ORIGINS=https://your-domain.example`
- `SESSION_DOMAIN=your-domain.example`
- `SANCTUM_STATEFUL_DOMAINS=your-domain.example`
- `VITE_API_URL=https://your-domain.example/api`
- secrets are stored in deployment environment variables, not in committed files
- preview environment uses separate database credentials from production
- webhook secrets are configured for YooKassa / Stripe / Telegram

## Deploy Steps

1. Run `composer install --no-dev --optimize-autoloader`
2. Run `npm ci`
3. Run `npm run build`
4. Run `php artisan migrate --force`
5. Run `php artisan storage:link || true`
6. Run `php artisan config:cache`
7. Run `php artisan route:cache`
8. Run `php artisan view:cache`
9. Run `php artisan queue:restart`

Preferred deploy script:

- `deploy/forge-deploy.sh`

## Worker and Scheduler

- At least `2` Redis queue workers are running
- Increase to `4` workers if queue latency rises
- `php artisan schedule:run` executes every minute

## Functional Smoke Test

- Home page loads on HTTPS
- SPA routes still work after browser refresh
- Student registration works
- Student login works
- Admin login works
- Olympiad request flow works
- Approved student can open the quiz
- Quiz submit creates a result
- Result appears in profile
- Certificate download works
- Admin can create and publish quizzes

## Operational Checks

- Redis is reachable from Laravel
- MySQL migrations are applied
- Queue worker does not crash after deploy
- Re-deploy completes without manual intervention
- Daily DB backups are enabled
- At least `7` daily backups are retained
- `.env` is not publicly accessible
- production database is not reachable from the public internet
- preview deployments cannot boot against the production DB
- `security.log` and `anomaly.log` are collected by your platform
- security headers are present on responses
- CORS does not use wildcard `*`
