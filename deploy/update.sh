#!/usr/bin/env bash
#
# Обновление уже установленного сайта до последней версии из GitHub.
#   sudo bash /var/www/eurika/deploy/update.sh
#
# Сайт на время обновления показывает страницу техработ, затем снова открывается.

set -Eeuo pipefail

APP_DIR="${APP_DIR:-/var/www/eurika}"
BRANCH="${BRANCH:-main}"

[[ $EUID -eq 0 ]] || { echo "Запустите через sudo."; exit 1; }
cd "$APP_DIR"

trap 'php artisan up >/dev/null 2>&1 || true; echo "Обновление прервано на строке $LINENO — сайт снова открыт на прежней версии кода."' ERR

echo "==> Скачиваю обновления"
git fetch --quiet origin "$BRANCH"
if [[ "$(git rev-parse HEAD)" == "$(git rev-parse "origin/$BRANCH")" ]]; then
  echo "Уже последняя версия."
  exit 0
fi

php artisan down --retry=30 >/dev/null
git reset --quiet --hard "origin/$BRANCH"

echo "==> Зависимости и сборка"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction --quiet
npm ci --no-audit --no-fund --loglevel=error
npm run build >/dev/null

echo "==> Проверка базы и миграции"
php artisan deploy:check-db >/dev/null || true
php artisan migrate --force

chown -R root:www-data "$APP_DIR"
chown -R www-data:www-data storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart >/dev/null

php artisan up >/dev/null
echo "==> Готово: $(git log -1 --format='%h %s')"
