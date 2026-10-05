#!/bin/sh
# Запуск контейнера приложения: ждём базу, применяем миграции (только в роли app),
# затем выполняем команду (php-fpm или queue:work).
set -e

cd /var/www/html

if [ -z "$APP_KEY" ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "[eurika] APP_KEY не задан. Укажите его в .env.docker (php artisan key:generate --show)." >&2
  exit 1
fi

echo "[eurika] Жду PostgreSQL ${DB_HOST}:${DB_PORT}..."
i=0
until php -r 'try { new PDO("pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Throwable $e) { exit(1); }'; do
  i=$((i + 1))
  [ "$i" -gt 60 ] && { echo "[eurika] База недоступна." >&2; exit 1; }
  sleep 1
done

if [ "${CONTAINER_ROLE:-app}" = "app" ]; then
  php artisan migrate --force
  php artisan storage:link >/dev/null 2>&1 || true
fi

exec "$@"
