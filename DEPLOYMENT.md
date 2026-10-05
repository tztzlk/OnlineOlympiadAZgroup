# Online Olympiad Deployment Guide

## Быстрый запуск на новом сервере (≈15 минут)

1. **Сервер.** Купите VPS в Казахстане (данные детей по закону РК должны храниться в РК):
   Ubuntu **24.04**, от 2 ГБ RAM, 1–2 vCPU, 30 ГБ диска. Подойдут hoster.kz, ps.kz и аналоги.
2. **Домен.** В панели hoster.kz (DNS домена `eurikaolympiads.com`) поменяйте A-записи
   `@` и `www` на IP нового сервера. Изменение расходится от нескольких минут до пары часов.
3. **Установка.** Подключитесь к серверу (`ssh root@IP`) и выполните:

   ```bash
   curl -fsSL https://raw.githubusercontent.com/tztzlk/OnlineOlympiadAZgroup/main/deploy/install-server.sh -o install.sh
   sudo DOMAIN=eurikaolympiads.com ADMIN_EMAIL=ваш@email bash install.sh
   ```

   Скрипт установит всё сам и в конце покажет адрес сайта, логин и пароль администратора.
   Если DNS ещё не обновился, сайт откроется по IP без HTTPS — запустите скрипт повторно
   позже, и он получит сертификат. Повторный запуск безопасен.
4. **Сохраните секреты** из `/root/eurika-credentials.txt` (особенно `APP_KEY`) в надёжное место.
5. **Почта.** Заполните `MAIL_*` в `/var/www/eurika/.env`, затем `cd /var/www/eurika && php artisan config:cache`.
6. **Обновления** после новых коммитов в `main`: `sudo bash /var/www/eurika/deploy/update.sh`.

Олимпиады создаются в админке (`/admin-login` → «Олимпиады»), можно импортировать из JSON или DOCX/PDF.
`php artisan db:seed` в продакшене **не запускать**: он создаёт администратора с паролем `password`.
Новых администраторов создавайте командой `php artisan admin:create email@пример --role=admin`.

This repository is intended to launch as one Laravel application serving both the Vue SPA and the API from the same domain.

## Production Architecture

Recommended first-launch architecture:

- 1 Ubuntu 24.04 VPS
- Nginx
- PHP-FPM 8.4
- MySQL 8
- same-origin domain for SPA and API
- database queue worker
- database or Redis cache/session depending on what the server provides

Do not split the SPA and API across separate hosts for the first launch. The current auth flow uses Sanctum personal access tokens stored in `localStorage`, not true cookie-first SPA auth.

## Recommended Hosting Options

Most practical:

1. Laravel Forge + a VPS provider
2. Manual VPS provisioning if you are comfortable managing Nginx, PHP-FPM, and Supervisor yourself



Recommended provider shapes:

- budget launch: 2 vCPU / 4 GB RAM
- safer public launch: 4 vCPU / 8 GB RAM

## Environment Strategy

Production must use one coherent `.env`. Duplicate keys should block deployment.

Minimum production values:

```dotenv
APP_NAME="Online Olympiad"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=online_olympiad
DB_USERNAME=olympiad_app
DB_PASSWORD=your_db_password
DB_SSLMODE=prefer

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=your-domain.com

QUEUE_CONNECTION=database
CACHE_STORE=database

CORS_ALLOWED_ORIGINS=https://your-domain.com
SANCTUM_STATEFUL_DOMAINS=your-domain.com
VITE_API_URL=https://your-domain.com/api

MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-user
MAIL_PASSWORD=your-mail-password
MAIL_FROM_ADDRESS=support@your-domain.com
MAIL_FROM_NAME="Online Olympiad"
SUPPORT_EMAIL=support@your-domain.com
SUPPORT_PHONE=+7 (777) 000-00-00

SECURITY_ENFORCE_HTTPS=true
YOOKASSA_WEBHOOK_SECRET=change-me
STRIPE_WEBHOOK_SECRET=change-me
TELEGRAM_WEBHOOK_SECRET_TOKEN=change-me
```

Notes:

- `SANCTUM_STATEFUL_DOMAINS` must contain hostnames only.
- `VITE_API_URL` must be set before `npm run build`.
- `MAIL_MAILER=log` is acceptable locally but should block a real production launch.
- If Redis is available, you may move `SESSION_DRIVER` and `CACHE_STORE` to `redis`, but keep the queue strategy explicit.

## First Deploy Sequence

```bash
cd /home/forge/your-domain.com

export COMPOSER_NO_INTERACTION=1
export NPM_CONFIG_FUND=false
export NPM_CONFIG_AUDIT=false

composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction
npm ci --no-audit --no-fund
npm run build

php artisan key:generate --force
php artisan deploy:check-db --connection=mysql
php artisan migrate --force
php artisan storage:link || true

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

The repository also ships `deploy/forge-deploy.sh` for Forge-based deployments.

If Forge shows `Deployment cancelled automatically because it was queued for too long`, the usual cause is that a previous deployment is still running or stuck. Open the site in Forge, inspect the latest active deployment log first, and only then retry or redeploy.

## Queue Worker

Current recommended worker command:

```bash
php artisan queue:work database --sleep=1 --tries=3 --max-time=3600
```

Example Supervisor program:

```ini
[program:online-olympiad-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/your-domain.com/artisan queue:work database --sleep=1 --tries=3 --max-time=3600
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

If you intentionally switch production to Redis queues, change the command and `.env` together.

## Nginx

Use the committed example at:

- `deploy/nginx/online-olympiad.conf.example`

Production requirements:

- document root must point to `public/`
- `try_files $uri $uri/ /index.php?$query_string;`
- deny access to hidden files such as `.env`
- `client_max_body_size 16m`
- PHP `upload_max_filesize` and `post_max_size` must exceed the Laravel upload limit

## Mail

Production mail must be real and tested before launch.

Required end-to-end checks:

- forgot-password email sends
- reset link opens the SPA correctly
- help desk submission sends to support inbox

Do not launch publicly with `MAIL_MAILER=log`.

## Storage

The admin quiz image upload stores files on the public disk.

Required:

- `php artisan storage:link`
- writable `storage/` and `bootstrap/cache/`
- Nginx and PHP upload limits aligned with application rules

## Security

This repository already includes:

- HTTPS redirect middleware
- strict security headers
- strict CORS allowlist
- proof-of-work protection for public forms
- webhook signature validation
- rate limiting and anomaly logging

- encryption at rest for child personal data: birth date (child profile and request), parent phone and email in requests (`app/Casts/EncryptedString.php`, `EncryptedDate.php`, migration `2026_10_04_000020_encrypt_child_personal_data`)
- per-account login throttling, constant-time login check, masked emails in security logs
- live olympiad payload never contains correct answers or explanations

Still required before public launch:

- final domain and HTTPS in `.env`
- real production mail
- no duplicate env keys
- no predictable imported-user password

### APP_KEY — обязательная резервная копия

Персональные данные детей шифруются ключом `APP_KEY`. **Если ключ потерян, эти данные не восстановить.**

- Храните копию `APP_KEY` вне сервера (менеджер паролей / сейф), отдельно от бэкапов базы.
- Никогда не запускайте `php artisan key:generate` на работающем сервере.
- Для ротации ключа: старый ключ перенесите в `APP_PREVIOUS_KEYS`, новый — в `APP_KEY`.

### PostgreSQL

Создание базы и отдельной роли приложения без прав суперпользователя:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\setup-postgres.ps1
php artisan migrate --force
```

Скрипт (`database/postgres/setup.sql`) закрывает подключение к базе всем ролям, кроме приложения, включает scram-sha-256 и таймауты запросов. В PHP должно быть включено расширение `pdo_pgsql`. На сервере используйте `DB_SSLMODE=require`, если БД на отдельной машине.

## Backups And Recovery

Minimum launch policy:

- daily database backup (`pg_dump -Fc`)
- at least 7 daily retained backups
- one tested restore before public launch
- VPS snapshot before schema-changing releases

## Monitoring

Minimum recommended monitoring:

- web server uptime
- queue worker process health
- `storage/logs/laravel.log`
- `storage/logs/security.log`
- `storage/logs/anomaly.log`
- alerts for repeated 401/403 spikes or worker crashes

Recommended next step:

- add Sentry, Flare, or another external exception tracker

## Smoke Test Before Go-Live

- home page loads over HTTPS
- hard refresh on SPA routes does not 404
- register, login, logout work
- password reset email works
- help desk email works
- parent can submit olympiad request
- admin can approve request
- admin can mark payment as paid
- approved and paid student can open quiz and submit
- certificate download works
- admin image upload works
- queue worker stays healthy after deploy

## Official References

- Laravel configuration: https://laravel.com/docs/12.x/configuration
- Laravel Sanctum: https://laravel.com/docs/12.x/sanctum
- Laravel queues: https://laravel.com/docs/12.x/queues
- Laravel file storage: https://laravel.com/docs/filesystem
- Laravel mail: https://laravel.com/docs/12.x/mail
- Laravel logging and errors: https://laravel.com/docs/12.x/logging and https://laravel.com/docs/12.x/errors
- Vite env and build: https://vite.dev/guide/env-and-mode and https://vite.dev/guide/build
- Laravel Forge deployments: https://forge.laravel.com/docs/sites/deployments
- Laravel Forge domains and SSL: https://forge.laravel.com/docs/sites/domains
- Nginx docs: https://nginx.org/en/docs/
- Certbot: https://certbot.eff.org/instructions
- MySQL backup and recovery: https://dev.mysql.com/doc/refman/5.7/en/backup-and-recovery.html
