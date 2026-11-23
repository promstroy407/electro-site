#!/usr/bin/env bash
# Создаёт .env боевого сервера из .env.example со случайными секретами.
# Секреты живут только в этом файле на сервере: в git и в чат не попадают.
# Существующий .env не трогает. Данные компании берутся из переменных
# окружения при вызове (их передаёт install.sh):
#   SITE_DOMAIN=example.ru ACME_EMAIL=... SITE_PHONE=... SITE_EMAIL=... deploy/make-env.sh
set -euo pipefail

cd "$(dirname "$0")/.."

if [ -f .env ]; then
    echo ".env уже есть — оставляю как есть"
    exit 0
fi

: "${SITE_DOMAIN:?Укажите SITE_DOMAIN, например SITE_DOMAIN=es12.ru}"
: "${ACME_EMAIL:?Укажите ACME_EMAIL — почта для писем от Let’s Encrypt}"

# shellcheck source=deploy/env-lib.sh
source deploy/env-lib.sh

umask 077
cp .env.example .env
# Переменные локального Sail (блок в конце шаблона) на сервере не нужны.
sed -i '/^# Docker (Sail)/,$d' .env

set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "https://${SITE_DOMAIN}"
set_env APP_KEY "base64:$(openssl rand -base64 32)"
set_env LOG_LEVEL warning

set_env DB_HOST pgsql
set_env DB_DATABASE electro
set_env DB_USERNAME electro
set_env DB_PASSWORD "$(openssl rand -hex 24)"
set_env REDIS_HOST redis

set_env SESSION_SECURE_COOKIE true

set_env SITE_DOMAIN "${SITE_DOMAIN}"
set_env ACME_EMAIL "${ACME_EMAIL}"
[ -n "${SITE_PHONE:-}" ] && set_env SITE_PHONE "\"${SITE_PHONE}\""
[ -n "${SITE_EMAIL:-}" ] && set_env SITE_EMAIL "${SITE_EMAIL}"
[ -n "${SITE_EMAIL:-}" ] && set_env SITE_NOTIFY_EMAIL "${SITE_EMAIL}"
[ -n "${SITE_TELEGRAM:-}" ] && set_env SITE_TELEGRAM "${SITE_TELEGRAM}"

echo "Создан .env с новыми случайными ключами (права 600)"
