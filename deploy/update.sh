#!/usr/bin/env bash
# Обновление сайта из ветки main. Запускается таймером каждые 5 минут
# (es12-update.timer) и вручную: deploy/update.sh --force — пересобрать
# и перезапустить, даже если новых коммитов нет.
set -euo pipefail

# Тело в функции: git reset ниже перезаписывает и этот файл, а bash
# читает скрипт по ходу выполнения. Функция разобрана целиком заранее.
main() {
    cd "$(dirname "$0")/.."

    # Второй заход — уже новой версией этого скрипта: блокировка
    # унаследована (дескриптор 9 открыт), код уже обновлён.
    if [ "${1:-}" != "--deploy" ]; then
        exec 9>/var/lock/es12-update.lock
        flock -n 9 || { echo "Обновление уже идёт"; exit 0; }

        git fetch --quiet origin main
        if [ "${1:-}" != "--force" ] && [ "$(git rev-parse HEAD)" = "$(git rev-parse origin/main)" ]; then
            exit 0
        fi

        echo "==> Обновляю до $(git rev-parse --short origin/main): $(git log -1 --format=%s origin/main)"
        git reset --hard --quiet origin/main

        # Дальше — шаги из только что скачанной версии скрипта, а не из старой.
        exec deploy/update.sh --deploy
    fi

    local compose=(docker compose --env-file .env -f deploy/compose.yaml)

    # Ключ IndexNow (сообщать Яндексу о новых страницах) создаём один раз.
    # Он не секрет: по протоколу лежит открыто на сайте, /<ключ>.txt.
    # shellcheck source=deploy/env-lib.sh
    source deploy/env-lib.sh
    if [ -z "$(env_value SITE_INDEXNOW_KEY)" ]; then
        set_env SITE_INDEXNOW_KEY "$(openssl rand -hex 16)"
        echo "==> Создан ключ IndexNow"
    fi

    "${compose[@]}" build --pull
    "${compose[@]}" up -d pgsql redis
    "${compose[@]}" run --rm app php artisan migrate --force
    "${compose[@]}" run --rm app php artisan db:seed --class='Database\Seeders\ContentSeeder' --force
    # Сначала PHP и очередь (запросы в это время ждут в Caddy), потом сам
    # Caddy — он перезапускается, только если поменялись картинки или стили.
    "${compose[@]}" up -d --no-deps app queue
    "${compose[@]}" up -d --remove-orphans

    echo "==> Проверка: сайт отвечает"
    for _ in $(seq 1 30); do
        if "${compose[@]}" exec -T web wget -qO- http://127.0.0.1:8080/up >/dev/null 2>&1; then
            echo "==> Готово: $(git rev-parse --short HEAD)"
            docker image prune -f >/dev/null
            # Сбой отправки не ломает обновление: адреса уйдут в следующий раз.
            "${compose[@]}" exec -T app php artisan seo:indexnow \
                || echo "[внимание] IndexNow: Яндекс не получил новые страницы, повторим при следующем обновлении" >&2
            exit 0
        fi
        sleep 2
    done

    echo "[ошибка] Сайт не ответил за минуту. Журнал: docker compose -f deploy/compose.yaml logs app web" >&2
    exit 1
}

main "$@"
