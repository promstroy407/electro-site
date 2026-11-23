# Общие функции для работы с .env на сервере. Подключать через source
# из скрипта, который уже перешёл в корень репозитория (cd .../..).

# Записать KEY=VALUE в .env: старая строка убирается, новая дописывается.
# Без sed, чтобы значения с / + | & не ломали замену. Права файла сохраняются.
set_env() {
    local key="$1" value="$2" tmp
    tmp="$(mktemp)"
    grep -v "^${key}=" .env > "$tmp" || true
    printf '%s=%s\n' "$key" "$value" >> "$tmp"
    cat "$tmp" > .env
    rm -f "$tmp"
}

# Прочитать значение KEY из .env без source (в значениях бывают пробелы),
# с внешними кавычками или без. Нет ключа — пустая строка.
env_value() {
    local value
    value="$(grep -m1 "^$1=" .env | cut -d= -f2-)" || true
    value="${value%\"}"
    value="${value#\"}"
    printf '%s' "$value"
}
