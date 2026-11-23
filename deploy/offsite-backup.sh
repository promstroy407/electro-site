#!/usr/bin/env bash
# Еженедельная копия наружу (es12-offsite.timer), чтобы данные пережили
# потерю сервера:
#   1) база — зашифрованным файлом на почту владельца (BACKUP_MAIL_TO);
#   2) фото и видео из админки — на Яндекс Диск владельца по WebDAV,
#      только новые и изменённые файлы. С Диска ничего не удаляется.
# Настройки и пароли — в .env, их записывает deploy/offsite-setup.sh.
# Расшифровка и восстановление — docs/server.md, раздел «Копии».
set -euo pipefail

cd "$(dirname "$0")/.."
# shellcheck source=deploy/env-lib.sh
source deploy/env-lib.sh

compose=(docker compose --env-file .env -f deploy/compose.yaml)
backups=/var/backups/es12
state_dir=/var/lib/es12
state="$state_dir/disk-sent.txt"
storage=/var/www/html/storage/app/public
dav_root=https://webdav.yandex.ru
# Папка на Диске владельца. Создаётся со всеми родителями (dav_mkdir).
disk_folder=/es12-kopii/foto

mail_to="$(env_value BACKUP_MAIL_TO)"
archive_password="$(env_value BACKUP_ARCHIVE_PASSWORD)"
disk_login="$(env_value YANDEX_DISK_LOGIN)"
disk_password="$(env_value YANDEX_DISK_APP_PASSWORD)"

if [ -z "$mail_to" ] || [ -z "$archive_password" ] || [ -z "$disk_login" ] || [ -z "$disk_password" ]; then
    echo "Копии наружу не настроены: запустите deploy/offsite-setup.sh" >&2
    exit 1
fi

failed=0

# ---------- 1. База на почту ----------

send_database() {
    local latest name
    latest="$(ls -1t "$backups"/db-*.sql.gz 2>/dev/null | head -1 || true)"
    # Ночная копия есть каждый день; если её нет или она старше суток — делаем сейчас.
    if [ -z "$latest" ] || [ -n "$(find "$latest" -mmin +1500)" ]; then
        deploy/backup.sh || return 1
        latest="$(ls -1t "$backups"/db-*.sql.gz | head -1)" || return 1
    fi
    name="$(basename "$latest").enc"

    # Пароль передаётся через окружение только процессу openssl, не в аргументах.
    BACKUP_ARCHIVE_PASSWORD="$archive_password" openssl enc -aes-256-cbc -pbkdf2 -iter 200000 -salt \
            -pass env:BACKUP_ARCHIVE_PASSWORD -in "$latest" \
        | "${compose[@]}" exec -T app php artisan backup:mail - --to="$mail_to" --name="$name"
}

# ---------- 2. Фото и видео на Яндекс Диск ----------

# Кодирует путь для адреса: всё, кроме латиницы, цифр и ._~/-, — в %XX.
urlencode() {
    local LC_ALL=C s="$1" out='' c i
    for ((i = 0; i < ${#s}; i++)); do
        c="${s:i:1}"
        case "$c" in
            [a-zA-Z0-9._~/-]) out+="$c" ;;
            *) printf -v c '%%%02X' "'$c"; out+="$c" ;;
        esac
    done
    printf '%s' "$out"
}

# Внутри функций, вызванных через ||, set -e не действует —
# поэтому ниже каждая важная команда проверяется явно (|| return 1).

# Запрос к Диску. Логин и пароль — через --config из дескриптора,
# чтобы не светились в списке процессов. Диск часто рвёт соединение
# (Connection reset) при частых запросах — повторяем до 5 раз с паузой.
dav() {
    curl -sS --retry 5 --retry-all-errors --retry-delay 15 --max-time 900 \
        --config <(printf 'user = "%s:%s"\n' "$disk_login" "$disk_password") "$@"
}

declare -A made_dirs=()

# Создаёт папку на Диске со всеми родителями по очереди (иначе WebDAV
# отвечает 409). 201 — создана, 405 — уже есть.
dav_mkdir() {
    local path="" part code
    IFS='/' read -ra parts <<< "$1"
    for part in "${parts[@]}"; do
        [ -n "$part" ] || continue
        path+="/$part"
        [ -n "${made_dirs[$path]:-}" ] && continue
        code="$(dav -o /dev/null -w '%{http_code}' -X MKCOL "$dav_root$(urlencode "$path")/")"
        case "$code" in
            201 | 405) made_dirs[$path]=1 ;;
            401) echo "Яндекс Диск не принял логин или пароль приложения" >&2; return 1 ;;
            *) echo "Яндекс Диск: не удалось создать папку $path (код $code)" >&2; return 1 ;;
        esac
    done
}

send_files() {
    local list tmp path size sent=0 total=0 errors=0
    mkdir -p "$state_dir"
    chmod 700 "$state_dir"
    touch "$state"

    dav_mkdir "$disk_folder" || return 1

    list="$(mktemp)"
    tmp="$(mktemp)"
    trap 'rm -f "$list" "$tmp"' RETURN

    # Список «путь<TAB>размер»; скрытые служебные файлы (.gitignore) пропускаем.
    "${compose[@]}" exec -T app find "$storage" -type f ! -name '.*' -printf '%P\t%s\n' < /dev/null | sort > "$list" \
        || { echo "Не удалось получить список файлов с сайта" >&2; return 1; }

    while IFS=$'\t' read -r path size; do
        total=$((total + 1))
        grep -qxF "$path"$'\t'"$size" "$state" && continue

        # Сбой одного файла не останавливает остальные: он догрузится
        # в следующий запуск, потому что не попал в список отправленных.
        if [ "$(dirname "$path")" != "." ] && ! dav_mkdir "$disk_folder/$(dirname "$path")"; then
            errors=$((errors + 1))
            continue
        fi
        if ! "${compose[@]}" exec -T app cat "$storage/$path" < /dev/null > "$tmp"; then
            echo "Не удалось прочитать $path" >&2
            errors=$((errors + 1))
            continue
        fi
        # Пауза между файлами, чтобы Диск не считал нас слишком частыми.
        sleep 2
        if dav -f -o /dev/null -T "$tmp" "$dav_root$(urlencode "$disk_folder/$path")"; then
            # Старую запись о файле с тем же путём убираем: размер изменился.
            grep -v "^$(printf '%s' "$path" | sed 's/[][\.*^$]/\\&/g')"$'\t' "$state" > "$state.new" || true
            printf '%s\t%s\n' "$path" "$size" >> "$state.new"
            mv "$state.new" "$state"
            sent=$((sent + 1))
        else
            echo "Яндекс Диск: не загрузился $path" >&2
            errors=$((errors + 1))
        fi
    done < "$list"

    echo "Яндекс Диск: загружено новых файлов — $sent, не загрузилось — $errors, всего файлов на сайте — $total"
    [ "$errors" -eq 0 ]
}

send_database || { echo "Копия базы на почту НЕ отправлена" >&2; failed=1; }
send_files || { echo "Фото и видео на Яндекс Диск отправлены НЕ все" >&2; failed=1; }

exit "$failed"
