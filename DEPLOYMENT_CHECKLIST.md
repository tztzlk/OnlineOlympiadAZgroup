# Online Olympiad Deployment Checklist

## Infrastructure

- Ubuntu 24.04 server provisioned
- Nginx installed
- PHP-FPM 8.4 installed
- MySQL 8 installed
- domain A record points to the server
- SSL certificate installed and working
- site root points to `public/`

## Production Environment

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- `FRONTEND_URL=https://your-domain.com`
- `DB_CONNECTION=mysql`
- `QUEUE_CONNECTION=database`
- `CACHE_STORE=database` or `redis`
- `SESSION_DRIVER=database` or `redis`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_DOMAIN=your-domain.com`
- `CORS_ALLOWED_ORIGINS=https://your-domain.com`
- `SANCTUM_STATEFUL_DOMAINS=your-domain.com`
- `VITE_API_URL=https://your-domain.com/api`
- `MAIL_MAILER` is real and not `log`
- webhook secrets configured
- no duplicate critical keys in `.env`

## Deploy Commands

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci`
3. `npm run build`
4. `php artisan key:generate --force` on first deploy only
5. `php artisan deploy:check-db --connection=mysql`
6. `php artisan migrate --force`
7. `php artisan storage:link || true`
8. `php artisan config:cache`
9. `php artisan route:cache`
10. `php artisan view:cache`
11. `php artisan queue:restart`

## Queue And Scheduler

- Supervisor is running database queue workers
- worker command matches `.env` queue driver
- cron or Forge scheduler runs `php artisan schedule:run` every minute

## Nginx And PHP

- using `deploy/nginx/online-olympiad.conf.example` or equivalent
- `try_files $uri $uri/ /index.php?$query_string;`
- hidden files denied
- `client_max_body_size` is at least `16m`
- PHP `upload_max_filesize` and `post_max_size` exceed Laravel upload requirements

## Functional Checks

- home page loads on HTTPS
- SPA routes survive hard refresh
- register, login, logout work
- forgot-password email works
- help desk submission sends mail
- parent can submit olympiad request
- admin can approve request
- admin can mark payment as paid
- approved and paid participant can open quiz
- quiz submission creates a result
- certificate download works
- admin image upload works

## Security And Operations

- imported users do not receive a shared default password
- logs are writable in `storage/logs`
- `security.log` and `anomaly.log` are being watched
- daily database backups are enabled
- at least one restore test has been completed
- production database is not publicly open to the internet
