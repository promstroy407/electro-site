#!/usr/bin/env bash
# Настройка еженедельной копии наружу (deploy/offsite-backup.sh).
# Запускать от root из /opt/es12: deploy/offsite-setup.sh
# Спрашивает почту для копий базы и доступ к Яндекс Диску, проверяет
# доступ, записывает всё в .env (права 600) и включает таймер.
# Пароли вводятся скрыто и никуда, кроме .env, не попадают.
# Повторный запуск безопасен: можно сменить почту или пароль Диска.
set -euo pipefail

cd "$(dirname "$0")/.."
root="$(pwd)"
# shellcheck source=deploy/env-lib.sh
source deploy/env-lib.sh

[ "$(id -u)" = 0 ] || { echo "Запустите от root" >&2; exit 1; }
[ -f .env ] || { echo "Нет .env — сначала deploy/install.sh" >&2; exit 1; }

ask() {
    local prompt="$1" default="$2" answer
    read -rp "$prompt${default:+ [$default]}: " answer
    printf '%s' "${answer:-$default}"
}

echo "==> Куда присылать копию базы"
default_mail="$(env_value BACKUP_MAIL_TO)"
[ -n "$default_mail" ] || default_mail="$(env_value SITE_NOTIFY_EMAIL)"
mail_to="$(ask "Почта" "$default_mail")"
[[ "$mail_to" =~ ^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$ ]] || { echo "Это не похоже на почту" >&2; exit 1; }

echo
echo "==> Яндекс Диск для фото и видео"
echo "Нужен пароль приложения Яндекса с доступом «Файлы (WebDAV)»:"
echo "id.yandex.ru → Безопасность → Пароли приложений → Файлы."
default_login="$(env_value YANDEX_DISK_LOGIN)"
[ -n "$default_login" ] || default_login="${mail_to%@*}"
disk_login="$(ask "Логин Яндекса" "$default_login")"
[[ "$disk_login" =~ ^[A-Za-z0-9._@-]{1,100}$ ]] || { echo "Логин — латиница, цифры, точка, дефис" >&2; exit 1; }

read -rsp "Пароль приложения для Диска (ввод не виден): " disk_password
echo
[[ "$disk_password" =~ ^[A-Za-z0-9]{8,64}$ ]] || { echo "Пароль приложения — латинские буквы и цифры, без пробелов" >&2; exit 1; }

echo "Проверяю доступ к Диску…"
code="$(curl -sS -o /dev/null -w '%{http_code}' --max-time 60 -X PROPFIND -H 'Depth: 0' \
    --config <(printf 'user = "%s:%s"\n' "$disk_login" "$disk_password") https://webdav.yandex.ru/)" || code=000
case "$code" in
    207) echo "Диск доступен." ;;
    401) echo "Яндекс не принял логин или пароль приложения — ничего не сохранено." >&2; exit 1 ;;
    *) echo "Диск не ответил (код $code) — ничего не сохранено, попробуйте позже." >&2; exit 1 ;;
esac

set_env BACKUP_MAIL_TO "$mail_to"
set_env YANDEX_DISK_LOGIN "$disk_login"
set_env YANDEX_DISK_APP_PASSWORD "$disk_password"

if [ -z "$(env_value BACKUP_ARCHIVE_PASSWORD)" ]; then
    # Случайный пароль: латиница и цифры, ~140 бит. Без него копию с почты
    # не открыть, а на сервере он пропадёт вместе с сервером — поэтому
    # владелец записывает его у себя.
    archive_password="$(openssl rand -base64 30 | tr -dc 'A-Za-z0-9' | cut -c1-24)"
    set_env BACKUP_ARCHIVE_PASSWORD "$archive_password"
    cat <<MSG

==> Пароль от копий базы (показывается один раз)

    $archive_password

Запишите его туда, где храните важные пароли, НЕ на этом сервере
и не в чат. Без него копию базы с почты не открыть.
MSG
    read -rp "Записали? Нажмите Enter… " _
    clear 2>/dev/null || true
else
    echo "Пароль от копий базы уже задан раньше — оставляю прежний."
fi

echo "==> Таймер: каждое воскресенье в 04:30"
cat > /etc/systemd/system/es12-offsite.service <<UNIT
[Unit]
Description=Копия базы на почту и фото на Яндекс Диск
After=docker.service network-online.target
Wants=network-online.target

[Service]
Type=oneshot
ExecStart=${root}/deploy/offsite-backup.sh
UNIT

cat > /etc/systemd/system/es12-offsite.timer <<UNIT
[Unit]
Description=Копия наружу раз в неделю

[Timer]
OnCalendar=Sun *-*-* 04:30:00
Persistent=true

[Install]
WantedBy=timers.target
UNIT

systemctl daemon-reload
systemctl enable --now es12-offsite.timer

echo
read -rp "Сделать первую копию сейчас (несколько минут)? [Д/н] " now
case "${now:-д}" in
    [нНnN]*) echo "Первая копия будет в воскресенье в 04:30." ;;
    *) deploy/offsite-backup.sh ;;
esac
