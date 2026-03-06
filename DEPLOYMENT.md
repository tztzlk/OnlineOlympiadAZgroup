# Deployment Guide — Online Olympiad

## Server Requirements

- Linux VPS (Ubuntu 22.04+ recommended)
- Nginx
- PHP 8.2+ with extensions: `mbstring`, `xml`, `bcmath`, `curl`, `mysql`, `zip`, `gd`
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+
- Supervisor (for queue workers)

---

## 1. Clone and Install Dependencies

```bash
cd /var/www
git clone <repository-url> online-olympiad
cd online-olympiad

composer install --no-dev --optimize-autoloader
npm ci
```

---

## 2. Environment Configuration

Copy the example env and configure it for production:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with production values:

```dotenv
APP_NAME="Online Olympiad"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=online_olympiad
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
SESSION_DOMAIN=.your-domain.com
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=your-domain.com

QUEUE_CONNECTION=database

VITE_APP_NAME="Online Olympiad"
VITE_API_URL=https://your-domain.com/api
```

### Key Environment Variables

| Variable | Description |
|---|---|
| `APP_URL` | Full public URL of the application |
| `APP_ENV` | Must be `production` |
| `APP_DEBUG` | Must be `false` in production |
| `DB_*` | MySQL connection credentials |
| `SESSION_DOMAIN` | Domain for session cookies (use `.your-domain.com` for subdomain support) |
| `SESSION_SECURE_COOKIE` | Set to `true` for HTTPS |
| `SANCTUM_STATEFUL_DOMAINS` | The SPA host only, no scheme (e.g. `your-domain.com`). Do not use full APP_URL. |
| `QUEUE_CONNECTION` | `database` (default) |
| `VITE_API_URL` | Full URL to the API base (e.g. `https://your-domain.com/api`) — **must be set explicitly** before `npm run build`; used at build time |
| `VITE_APP_NAME` | Application name for the frontend |

---

## 3. Build Frontend Assets

The frontend **must be built before deployment** since `VITE_*` variables are embedded at build time:

```bash
npm run build
```

This outputs compiled assets to `public/build/`. Laravel loads them via the `@vite` Blade directive in `welcome.blade.php`.

---

## 4. Database Setup

```bash
php artisan migrate --force
```

---

## 5. Laravel Production Optimization

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set correct permissions:

```bash
chown -R www-data:www-data /var/www/online-olympiad
chmod -R 755 /var/www/online-olympiad/storage
chmod -R 755 /var/www/online-olympiad/bootstrap/cache
```

---

## 6. Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/online-olympiad/public;

    ssl_certificate     /etc/ssl/certs/your-domain.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.key;

    index index.php;

    charset utf-8;

    # Handle API and Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Test and reload:

```bash
nginx -t
systemctl reload nginx
```

---

## 7. Queue Worker (Supervisor)

Create a Supervisor config at `/etc/supervisor/conf.d/online-olympiad-worker.conf`:

```ini
[program:online-olympiad-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/online-olympiad/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/online-olympiad/storage/logs/worker.log
stopwaitsecs=3600
```

Enable and start:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start online-olympiad-worker:*
```

---

## 8. Cron (Task Scheduler)

Add to the `www-data` crontab:

```bash
crontab -u www-data -e
```

```cron
* * * * * cd /var/www/online-olympiad && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Post-Deployment Checklist

- [ ] `.env` is configured with production values
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` is generated
- [ ] Database migrated
- [ ] `npm run build` completed (assets in `public/build/`)
- [ ] Config/route/view caches generated
- [ ] Nginx configured and reloaded
- [ ] SSL certificate installed
- [ ] Supervisor running queue workers
- [ ] Cron job registered
- [ ] File permissions set correctly
- [ ] `.env` is NOT accessible from the web

---

## Updating the Application

```bash
cd /var/www/online-olympiad

git pull origin main

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

supervisorctl restart online-olympiad-worker:*
```
