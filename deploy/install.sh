#!/usr/bin/env bash
# Первый запуск на чистом сервере (Ubuntu 24.04, Docker уже стоит).
# Запускать от root из папки репозитория, данные компании — переменными:
#   SITE_DOMAIN=es12.ru ACME_EMAIL=... SITE_PHONE="..." SITE_EMAIL=... deploy/install.sh
# Повторный запуск безопасен: .env не перезаписывается.
set -euo pipefail

cd "$(dirname "$0")/.."
root="$(pwd)"

[ "$(id -u)" = 0 ] || { echo "Запустите от root" >&2; exit 1; }
command -v docker >/dev/null || { echo "Docker не установлен" >&2; exit 1; }

echo "==> Файл .env"
deploy/make-env.sh

echo "==> Git забирает обновления по https: репозиторий публичный, ключ не нужен"
git remote set-url origin https://github.com/promstroy407/electro-site.git

echo "==> Таймеры: обновление каждые 5 минут, копия базы каждую ночь"
cat > /etc/systemd/system/es12-update.service <<UNIT
[Unit]
Description=Обновление сайта из GitHub
After=docker.service network-online.target
Wants=network-online.target

[Service]
Type=oneshot
ExecStart=${root}/deploy/update.sh
UNIT

cat > /etc/systemd/system/es12-update.timer <<UNIT
[Unit]
Description=Проверять обновления сайта каждые 5 минут

[Timer]
OnBootSec=2min
OnUnitActiveSec=5min

[Install]
WantedBy=timers.target
UNIT

cat > /etc/systemd/system/es12-backup.service <<UNIT
[Unit]
Description=Копия базы и фото сайта
After=docker.service

[Service]
Type=oneshot
ExecStart=${root}/deploy/backup.sh
UNIT

cat > /etc/systemd/system/es12-backup.timer <<UNIT
[Unit]
Description=Копия базы и фото сайта каждую ночь

[Timer]
OnCalendar=*-*-* 03:30:00
Persistent=true

[Install]
WantedBy=timers.target
UNIT

systemctl daemon-reload
systemctl enable --now es12-update.timer es12-backup.timer

echo "==> Сборка и запуск (первый раз 5–10 минут)"
deploy/update.sh --force

cat <<DONE

Сайт запущен. Осталось создать вход в админку:
  docker compose --env-file .env -f deploy/compose.yaml exec app php artisan admin:create ВАША_ПОЧТА
И включить недельную копию на почту и Яндекс Диск:
  deploy/offsite-setup.sh
DONE
