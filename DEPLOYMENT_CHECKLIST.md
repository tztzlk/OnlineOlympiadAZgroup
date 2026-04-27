# Deployment Checklist - Current Repository

Use this checklist for the current codebase, not for older deployment notes.

## 1. Infrastructure

- Ubuntu 24.04 server or equivalent is provisioned
- Nginx is installed
- PHP-FPM 8.4 is installed
- MySQL 8 is installed and reachable from the app server
- Node/npm is installed for frontend builds
- domain points to the server
- SSL certificate is active
- document root points to `public/`

## 2. Environment File

The repository currently does not ship `.env.example`, so the production `.env` must be prepared carefully.

Required minimum values:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- `FRONTEND_URL=https://your-domain.com`
- `APP_KEY` is generated

- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

- `SESSION_DRIVER=database` or `redis`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`
- `SESSION_DOMAIN=your-domain.com`

- `CACHE_STORE=database` or `redis`
- `QUEUE_CONNECTION=database` unless you intentionally deploy Redis workers

- `CORS_ALLOWED_ORIGINS=https://your-domain.com`
- `SANCTUM_STATEFUL_DOMAINS=your-domain.com`
- `VITE_API_URL=https://your-domain.com/api`

- `MAIL_MAILER` is real and not `log`
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` are set
- webhook secrets are configured

Validation rules:

- no duplicate critical keys in `.env`
- `SANCTUM_STATEFUL_DOMAINS` contains hosts, not paths
- `VITE_API_URL` is set before frontend build

## 3. Deploy Commands

Run the canonical deploy flow from `deploy/forge-deploy.sh` or keep Forge inline commands identical to it.

Order:

1. `composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction`
2. `npm ci --no-audit --no-fund`
3. `npm run build`
4. `php artisan deploy:check-db --connection=mysql`
5. `php artisan migrate --force`
6. `php artisan storage:link || true`
7. `php artisan config:clear`
8. `php artisan route:clear`
9. `php artisan view:clear`
10. `php artisan config:cache`
11. `php artisan route:cache`
12. `php artisan view:cache`
13. `php artisan queue:restart`

First deploy only:

- `php artisan key:generate --force`

## 4. Queue And Scheduler

- queue worker is running under Supervisor or Forge worker management
- worker command matches the actual queue driver
- if using database queues, worker command is based on `queue:work database`
- scheduler runs `php artisan schedule:run` every minute
- failed jobs are monitored
- queue backlog is visible somewhere operationally

## 5. Nginx / PHP

- using `deploy/nginx/online-olympiad.conf.example` or an equivalent config
- `try_files $uri $uri/ /index.php?$query_string;` is present
- hidden files are denied
- `client_max_body_size` is at least `16m`
- PHP `upload_max_filesize` and `post_max_size` are aligned with quiz image uploads
- `storage/` and `bootstrap/cache/` are writable

## 6. Security Checks

- HTTPS redirect works
- security headers are present
- `APP_DEBUG=false`
- `APP_KEY` is set
- production DB is not publicly open
- imported users do not share a predictable password
- local token auth risk is understood and CSP/XSS protections are taken seriously

## 7. Functional Smoke Test

Public:

- home page loads on HTTPS
- SPA routes survive hard refresh
- subjects list works
- leaderboard works
- certificate check works

Auth:

- register works
- login works
- logout works
- forgot-password mail sends
- reset password flow works
- unauthenticated `/api/profile` returns JSON `401`, not HTML redirect or `500`

User flow:

- parent can create/update profile
- parent can add child
- parent can submit olympiad request
- payment status can be updated through the intended admin flow
- approved and paid participant can open quiz
- quiz submission creates result
- certificate preview/download works
- result mistakes page works when available

Admin flow:

- admin login works
- admin dashboard loads
- admin requests page loads
- admin can approve request
- admin can mark payment as paid
- admin quiz image upload works

Operational:

- queue worker keeps processing after deploy
- `storage/logs/laravel.log` is writable
- `storage/logs/security.log` is writable
- `storage/logs/anomaly.log` is writable

## 8. Pre-Wider-Launch Extras

Before broad public traffic, not just a soft launch:

- CI/CD pipeline exists
- `.env.example` is committed
- backup restore test has been performed
- queue / scheduler health checks exist
- at least one scripted smoke suite exists
