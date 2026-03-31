# Deployment Guide - Online Olympiad

This project is designed to be deployed as a single Laravel application that serves both the SPA frontend and the API from the same domain.

## Production Security Baseline

This repository now includes runtime hardening for production deployments:

- forced HTTPS redirect with HSTS
- strict CORS via `CORS_ALLOWED_ORIGINS`
- security headers: `X-Frame-Options`, `X-Content-Type-Options`, CSP, Referrer-Policy
- structured JSON logs for auth attempts, API errors, suspicious activity, and request spikes
- preview environment guard that blocks boot if a Vercel preview deploy points to the production database

These controls are configured through environment variables and should be enabled in production before go-live.

## Recommended Production Stack

- `Laravel Forge` for provisioning and deploy automation
- `1 Linux VPS` (Ubuntu 22.04/24.04)
- `Nginx + PHP-FPM`
- `PHP 8.2+`
- `MySQL 8`
- `Redis` for sessions, cache, and queues
- `Supervisor` for queue workers
- `Let's Encrypt` for SSL

## Recommended Server Size

For the first public launch and roughly `500+` online users:

- `4 vCPU`
- `8 GB RAM`
- `80+ GB NVMe SSD`

If the audience is mostly in Kazakhstan or Central Asia, choose the closest region available from your VPS provider.

## 1. Domain and DNS

1. Buy a domain such as `example.kz`, `example.com`, or `example.org`.
2. Create an `A` record pointing the root domain to your server IP.
3. Wait for DNS propagation before enabling SSL in Forge.

Recommended setup:

- App URL: `https://your-domain.com`
- Frontend: same origin as backend
- API base: `https://your-domain.com/api`

Using the same domain for the SPA and API keeps authentication, cookies, and deployment much simpler.

## 2. Provision the Server in Forge

1. Create a new server in Forge.
2. Choose Ubuntu 22.04 or 24.04.
3. Install:
   - PHP 8.2+
   - Nginx
   - MySQL
   - Redis
4. Create a new site with the web root set to:

```bash
/home/forge/your-domain.com/public
```

5. Connect the Git repository to the Forge site.
6. Enable SSL through Forge after DNS is pointed correctly.

## 3. Production Environment

Copy `.env.example` to `.env` and configure production values.

Minimum recommended production config:

```dotenv
APP_NAME="Online Olympiad"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com

SECURITY_ENFORCE_HTTPS=true
CORS_ALLOWED_ORIGINS=https://your-domain.com
SESSION_SECURE_COOKIE=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=online_olympiad
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=redis
SESSION_DOMAIN=your-domain.com
SESSION_SECURE_COOKIE=true

CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

SANCTUM_STATEFUL_DOMAINS=your-domain.com

VITE_APP_NAME="Online Olympiad"
VITE_API_URL=https://your-domain.com/api

YOOKASSA_WEBHOOK_SECRET=change-me
STRIPE_WEBHOOK_SECRET=change-me
TELEGRAM_WEBHOOK_SECRET_TOKEN=change-me
```

Important notes:

- `APP_DEBUG` must be `false`.
- Secrets must be stored only in deployment environment variables, not committed to the repository.
- `SANCTUM_STATEFUL_DOMAINS` must contain hostnames only, no scheme or path.
- `VITE_API_URL` must be set explicitly before `npm run build`.
- `SESSION_DOMAIN=your-domain.com` is enough for same-origin deployment.
- Use `.your-domain.com` only if you need cookies across subdomains.

For Vercel + managed database deployments:

- store secrets in `Vercel Environment Variables`
- for Supabase, keep database credentials in `Supabase Vault` / project secrets
- never place webhook secrets, DB URLs, API keys, or app keys in source files

## 3.1 Database Exposure Rules

Production database access must not be publicly open:

- allow inbound database traffic only from the application network / known IPs
- do not expose MySQL/Postgres directly to the public internet
- if you use Supabase, keep row-level access and network exposure limited to the platform defaults
- if you use a VPS database, bind it to a private interface or firewall it to trusted sources only

## 3.2 Vercel Preview Isolation

Preview deployments must not share the production database.

Set these variables on Vercel:

```dotenv
VERCEL_ENV=preview
PRODUCTION_DATABASE_URL=postgres://prod-db-url
PRODUCTION_DB_HOST=prod-db-host
PRODUCTION_DB_DATABASE=prod-db-name
```

The application now refuses to boot if a preview deployment is wired to the same production database URL or the same production host/database pair.

## 4. First Deploy

Forge can use the deploy script from `deploy/forge-deploy.sh` as the baseline.

If you want to run the commands manually, use:

```bash
cd /home/forge/your-domain.com

composer install --no-dev --optimize-autoloader
npm ci
npm run build

php artisan key:generate --force
php artisan migrate --force
php artisan storage:link || true

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. Queue Workers

Use Redis-backed workers in Forge or Supervisor.

Recommended worker command:

```bash
php artisan queue:work redis --sleep=1 --tries=3 --max-time=3600
```

Recommended process count:

- Start with `2` workers
- Increase to `4` if queue latency grows under load

Example Supervisor config:

```ini
[program:online-olympiad-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/your-domain.com/artisan queue:work redis --sleep=1 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=forge
numprocs=2
redirect_stderr=true
stdout_logfile=/home/forge/your-domain.com/storage/logs/worker.log
stopwaitsecs=3600
```

## 6. Scheduler

Forge can manage the scheduler automatically. If you configure it manually, add:

```cron
* * * * * cd /home/forge/your-domain.com && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Backups

Minimum backup policy:

- Daily MySQL backup
- Keep at least `7` daily backups
- Keep VPS snapshots before large releases or schema changes

If your VPS provider supports automatic snapshots, enable them.

If you use Supabase:

- verify that daily automatic backups are enabled on the project
- confirm retention policy matches your recovery requirements
- test at least one restore procedure before launch

## 8. Performance Notes for Launch

Production defaults in this repository now assume:

- `Redis` for sessions
- `Redis` for cache
- `Redis` for queues
- `MySQL` for business data

Additional launch guidance:

- Keep frontend and API on the same origin
- Run `npm run build` on every frontend or `VITE_*` change
- Restart queue workers after deploy
- Monitor MySQL, Redis, PHP-FPM memory, queue latency, and `security.log` / `anomaly.log`

Recommended monitoring sinks:

- Vercel log drains / platform logs
- Sentry or similar error tracking for exceptions
- Supabase database monitoring and backup status
- alerts on spikes in `401/403`, high request rates, and repeated auth failures

## 9. Pre-Launch Smoke Test

Before opening the site publicly, verify:

- Home page loads over HTTPS
- SPA routes work after refresh
- Student registration works
- Student login works
- Admin login works
- Olympiad request flow works
- Approved user can open quiz subjects
- Quiz start and submit work
- Result appears in profile
- Certificate download works
- Admin can create and publish quizzes
- Queue workers are running
- Re-deploy completes without manual fixes

## 10. Updating the Application

Recommended update sequence:

```bash
cd /home/forge/your-domain.com
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## 11. Notes About the Current Codebase

These database migrations already exist in the project:

- `jobs`
- `cache`
- `failed_jobs` support through Laravel defaults

The project is ready for a standard Forge deployment with Redis enabled. No separate frontend host or container platform is required for the first production launch.
