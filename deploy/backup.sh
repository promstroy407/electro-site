#!/usr/bin/env bash
# Ежедневная копия базы и загруженных фото (es12-backup.timer).
# Хранится 14 дней в /var/backups/es12 на этом же сервере — это защита
# от ошибки в данных, но не от потери сервера: копию стоит забирать наружу.
set -euo pipefail

cd "$(dirname "$0")/.."

dir=/var/backups/es12
stamp="$(date +%F)"
compose=(docker compose --env-file .env -f deploy/compose.yaml)

mkdir -p "$dir"
chmod 700 "$dir"

"${compose[@]}" exec -T pgsql sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' \
    | gzip > "$dir/db-$stamp.sql.gz"
"${compose[@]}" exec -T app tar -C /var/www/html/storage/app -czf - public \
    > "$dir/files-$stamp.tar.gz"

find "$dir" -name '*.gz' -mtime +14 -delete
echo "Копия готова: $dir/db-$stamp.sql.gz, $dir/files-$stamp.tar.gz"
