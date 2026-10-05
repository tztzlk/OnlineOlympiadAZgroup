#!/usr/bin/env bash

set -euo pipefail

cd /home/forge/your-domain.com

if [ ! -f .env ]; then
  echo ".env is missing"
  exit 1
fi

if ! grep -q '^APP_ENV=production$' .env; then
  echo "APP_ENV must be set to production before deploy"
  exit 1
fi

if grep -q '^APP_DEBUG=true$' .env; then
  echo "APP_DEBUG must be false in production"
  exit 1
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  echo "APP_KEY is missing or not generated"
  exit 1
fi

if grep -q '^MAIL_MAILER=log$' .env; then
  echo "MAIL_MAILER=log is not acceptable for a real production launch"
  exit 1
fi

export COMPOSER_NO_INTERACTION=1
export NPM_CONFIG_FUND=false
export NPM_CONFIG_AUDIT=false

composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction
npm ci --no-audit --no-fund
npm run build

# Подключение берётся из .env сервера: скрипт работает и с MySQL, и с PostgreSQL.
DB_CONNECTION_NAME="$(grep -E '^DB_CONNECTION=' .env | tail -n1 | cut -d= -f2- | tr -d '"'"'"' \r')"
php artisan deploy:check-db --connection="${DB_CONNECTION_NAME:-pgsql}"
php artisan migrate --force
php artisan storage:link || true

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
