#!/usr/bin/env bash
#
# Полная установка Eurika на чистый сервер Ubuntu 24.04 одной командой.
#
#   curl -fsSL https://raw.githubusercontent.com/tztzlk/OnlineOlympiadAZgroup/main/deploy/install-server.sh -o install.sh
#   sudo DOMAIN=eurikaolympiads.com ADMIN_EMAIL=you@example.com bash install.sh
#
# Что делает: nginx + PHP 8.3 + PostgreSQL + Node 22, фаервол, код с GitHub,
# база и роль без прав суперпользователя, .env со случайными секретами,
# миграции, сборка фронтенда, HTTPS (Let's Encrypt), очередь, ежедневный бэкап БД,
# администратор со случайным паролем. Повторный запуск безопасен.

set -Eeuo pipefail

DOMAIN="${DOMAIN:-}"
ADMIN_EMAIL="${ADMIN_EMAIL:-}"
REPO_URL="${REPO_URL:-https://github.com/tztzlk/OnlineOlympiadAZgroup.git}"
BRANCH="${BRANCH:-main}"
APP_DIR="${APP_DIR:-/var/www/eurika}"
DB_NAME="online_olympiad"
DB_USER="olympiad_app"
CREDENTIALS_FILE="/root/eurika-credentials.txt"

log()  { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m[!] %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31m[x] %s\033[0m\n' "$*" >&2; exit 1; }
trap 'die "Ошибка на строке $LINENO. Установка остановлена — исправьте причину и запустите скрипт снова."' ERR

[[ $EUID -eq 0 ]] || die "Запустите через sudo."
[[ -n "$DOMAIN" ]] || die "Укажите домен: sudo DOMAIN=eurikaolympiads.com ADMIN_EMAIL=you@example.com bash install.sh"
[[ -n "$ADMIN_EMAIL" ]] || die "Укажите ADMIN_EMAIL — на него придут уведомления Let's Encrypt, он же станет логином администратора."
[[ "$ADMIN_EMAIL" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]] || die "ADMIN_EMAIL выглядит некорректно: $ADMIN_EMAIL"
[[ "$DOMAIN" =~ ^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]] || die "DOMAIN выглядит некорректно: $DOMAIN (нужно без https:// и слэшей)"
grep -q 'Ubuntu 24' /etc/os-release || warn "Скрипт проверен на Ubuntu 24.04 — на другой версии могут понадобиться правки."

export DEBIAN_FRONTEND=noninteractive

# ---------------------------------------------------------------- пакеты
log "Устанавливаю системные пакеты (nginx, PHP 8.3, PostgreSQL, certbot)"
apt-get update -y
apt-get install -y --no-install-recommends \
  ca-certificates curl git unzip gnupg ufw cron \
  nginx postgresql postgresql-contrib \
  php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath php8.3-sqlite3 \
  certbot python3-certbot-nginx

if ! command -v composer >/dev/null; then
  log "Устанавливаю Composer"
  curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
  php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
  rm -f /tmp/composer-setup.php
fi

if ! command -v node >/dev/null || [[ "$(node -v | cut -c2- | cut -d. -f1)" -lt 20 ]]; then
  log "Устанавливаю Node.js 22 (нужен для сборки фронтенда)"
  curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
  apt-get install -y nodejs
fi

# ---------------------------------------------------------------- фаервол
log "Настраиваю фаервол: открыты только SSH, HTTP и HTTPS"
ufw allow OpenSSH >/dev/null
ufw allow 'Nginx Full' >/dev/null
ufw --force enable >/dev/null

# ---------------------------------------------------------------- PHP
log "Усиливаю настройки PHP"
PHP_INI=/etc/php/8.3/fpm/conf.d/99-eurika.ini
cat > "$PHP_INI" <<'INI'
expose_php = Off
upload_max_filesize = 25M
post_max_size = 26M
memory_limit = 256M
max_execution_time = 120
session.cookie_httponly = 1
session.cookie_secure = 1
INI
systemctl restart php8.3-fpm

# ---------------------------------------------------------------- код
log "Скачиваю код с GitHub ($BRANCH)"
if [[ -d "$APP_DIR/.git" ]]; then
  git -C "$APP_DIR" fetch --quiet origin "$BRANCH"
  git -C "$APP_DIR" reset --quiet --hard "origin/$BRANCH"
else
  mkdir -p "$(dirname "$APP_DIR")"
  git clone --quiet --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
fi
git config --global --add safe.directory "$APP_DIR"

# ---------------------------------------------------------------- база
log "Создаю базу PostgreSQL и роль приложения без прав суперпользователя"
if [[ -f "$CREDENTIALS_FILE" ]] && grep -q '^DB_PASSWORD=' "$CREDENTIALS_FILE"; then
  DB_PASSWORD="$(grep '^DB_PASSWORD=' "$CREDENTIALS_FILE" | cut -d= -f2-)"
else
  DB_PASSWORD="$(openssl rand -base64 48 | tr -dc 'A-Za-z0-9' | head -c 32)"
fi
sudo -u postgres psql -q -v ON_ERROR_STOP=1 -v app_password="'$DB_PASSWORD'" -f "$APP_DIR/database/postgres/setup.sql"

# ---------------------------------------------------------------- .env
cd "$APP_DIR"
if [[ ! -f .env ]]; then
  log "Создаю .env со случайными секретами"
  sed -e "s|__DOMAIN__|$DOMAIN|g" \
      -e "s|__DB_PASSWORD__|$DB_PASSWORD|g" \
      -e "s|__ENFORCE_HTTPS__|false|g" \
      deploy/.env.production.example > .env
  FRESH_ENV=1
else
  warn ".env уже существует — оставляю как есть (секреты не перезаписываются)."
  FRESH_ENV=0
fi
chmod 640 .env

# ---------------------------------------------------------------- сборка
log "Устанавливаю зависимости и собираю фронтенд (несколько минут)"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction --quiet
npm ci --no-audit --no-fund --loglevel=error
npm run build >/dev/null

if [[ "$FRESH_ENV" == "1" ]]; then
  php artisan key:generate --force
fi

log "Применяю миграции"
php artisan migrate --force
php artisan storage:link >/dev/null 2>&1 || true

# Права: код принадлежит root, писать веб-сервер может только в storage и кэш.
chown -R root:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} +
find "$APP_DIR" -type f -exec chmod 644 {} +
chmod 640 "$APP_DIR/.env"
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# ---------------------------------------------------------------- nginx
log "Настраиваю nginx для $DOMAIN"
sed -e "s|__DOMAIN__|$DOMAIN|g" -e "s|__APP_DIR__|$APP_DIR|g" \
    deploy/nginx/eurika.conf.template > /etc/nginx/sites-available/eurika.conf
ln -sf /etc/nginx/sites-available/eurika.conf /etc/nginx/sites-enabled/eurika.conf
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

# ---------------------------------------------------------------- HTTPS
log "Получаю HTTPS-сертификат Let's Encrypt"
SERVER_IP="$(curl -fsS4 https://api.ipify.org || true)"
DOMAIN_IP="$(getent ahostsv4 "$DOMAIN" | awk 'NR==1{print $1}' || true)"
HTTPS_OK=0
if [[ -n "$SERVER_IP" && "$SERVER_IP" == "$DOMAIN_IP" ]]; then
  CERT_DOMAINS=(-d "$DOMAIN")
  if [[ "$(getent ahostsv4 "www.$DOMAIN" | awk 'NR==1{print $1}' || true)" == "$SERVER_IP" ]]; then
    CERT_DOMAINS+=(-d "www.$DOMAIN")
  fi
  if certbot --nginx "${CERT_DOMAINS[@]}" --redirect --non-interactive --agree-tos -m "$ADMIN_EMAIL"; then
    HTTPS_OK=1
    sed -i 's/^SECURITY_ENFORCE_HTTPS=.*/SECURITY_ENFORCE_HTTPS=true/' .env
    sed -i 's/^SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=true/' .env
  fi
else
  warn "Домен $DOMAIN указывает на ${DOMAIN_IP:-никуда}, а этот сервер — $SERVER_IP."
  warn "Поменяйте A-запись домена на $SERVER_IP и запустите скрипт ещё раз — он получит сертификат."
  sed -i 's/^SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=false/' .env
fi

# ---------------------------------------------------------------- кэш и очередь
log "Кэширую конфигурацию и запускаю обработчик очереди"
php artisan config:cache
php artisan route:cache
php artisan view:cache

sed -e "s|__APP_DIR__|$APP_DIR|g" deploy/systemd/eurika-queue.service > /etc/systemd/system/eurika-queue.service
systemctl daemon-reload
systemctl enable --now eurika-queue >/dev/null 2>&1
php artisan queue:restart >/dev/null

# Планировщик Laravel (на будущее) и ежедневный бэкап базы с хранением 14 дней.
cat > /etc/cron.d/eurika <<CRON
* * * * * www-data cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1
30 3 * * * postgres pg_dump -Fc $DB_NAME > /var/backups/eurika-\$(date +\%F).dump && find /var/backups -name 'eurika-*.dump' -mtime +14 -delete
CRON
chmod 644 /etc/cron.d/eurika

# ---------------------------------------------------------------- администратор
ADMIN_PASSWORD=""
if ! sudo -u postgres psql -d "$DB_NAME" -tAc "select 1 from users where email = '$ADMIN_EMAIL'" | grep -q 1; then
  log "Создаю администратора $ADMIN_EMAIL"
  ADMIN_PASSWORD="$(php artisan admin:create "$ADMIN_EMAIL" --name="Администратор" | sed -n 's/^Пароль.*: //p')"
fi

# ---------------------------------------------------------------- итог
APP_KEY_VALUE="$(grep '^APP_KEY=' .env | cut -d= -f2-)"
[[ -f "$CREDENTIALS_FILE" ]] && cp "$CREDENTIALS_FILE" "$CREDENTIALS_FILE.prev"
{
  echo "# Eurika — секреты сервера. Храните копию вне сервера, затем можно удалить этот файл."
  echo "DOMAIN=$DOMAIN"
  echo "APP_KEY=$APP_KEY_VALUE"
  echo "DB_PASSWORD=$DB_PASSWORD"
  if [[ -n "$ADMIN_PASSWORD" ]]; then
    echo "ADMIN_EMAIL=$ADMIN_EMAIL"
    echo "ADMIN_PASSWORD=$ADMIN_PASSWORD"
  elif [[ -f "$CREDENTIALS_FILE.prev" ]]; then
    grep '^ADMIN_' "$CREDENTIALS_FILE.prev" || true
  fi
} > "$CREDENTIALS_FILE.new"
mv "$CREDENTIALS_FILE.new" "$CREDENTIALS_FILE"
chmod 600 "$CREDENTIALS_FILE"

log "Готово"
if [[ "$HTTPS_OK" == "1" ]]; then
  echo "Сайт:        https://$DOMAIN"
else
  echo "Сайт:        http://$SERVER_IP  (HTTPS появится после смены A-записи домена и повторного запуска)"
fi
echo "Админка:     /admin-login"
if [[ -n "$ADMIN_PASSWORD" ]]; then
  echo "Логин:       $ADMIN_EMAIL"
  echo "Пароль:      $ADMIN_PASSWORD   (показывается один раз — сохраните)"
fi
echo
echo "Секреты (APP_KEY, пароль БД, пароль администратора) сохранены в $CREDENTIALS_FILE"
echo "ОБЯЗАТЕЛЬНО скопируйте APP_KEY в надёжное место: без него данные детей не расшифровать."
grep -q '^MAIL_MAILER=log' .env && warn "Почта не настроена (MAIL_MAILER=log): заполните MAIL_* в $APP_DIR/.env и выполните: php artisan config:cache"
exit 0
