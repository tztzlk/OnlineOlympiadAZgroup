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

composer install --no-dev --optimize-autoloader
npm ci
npm run build

php artisan migrate --force
php artisan storage:link || true

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
