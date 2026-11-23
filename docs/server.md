# Боевой сервер

FirstVDS, Москва (данные клиентов по 152-ФЗ хранятся в России).
Ubuntu 24.04, Docker. Сайт: https://es12.ru.

## Как устроено

- Код лежит в `/opt/es12` — копия репозитория. Репозиторий публичный,
  сервер забирает его из GitHub по https без ключа.
- Каждые 5 минут таймер `es12-update.timer` запускает
  `deploy/update.sh`: если в `main` есть новое — пересобирает образ,
  применяет миграции, перезапускает контейнеры и проверяет, что сайт
  отвечает. То есть **слитый pull request сам появляется на сайте**.
- Контейнеры (`deploy/compose.yaml`): `web` (Caddy: HTTPS и статика),
  `app` (PHP), `queue` (уведомления о заявках), `pgsql`, `redis`.
- HTTPS-сертификат Caddy получает и продлевает сам.
- Секреты — только в `/opt/es12/.env` (права 600). Его создал
  `deploy/make-env.sh` со случайными ключами; в git его нет.
- Каждую ночь в 03:30 `es12-backup.timer` кладёт копию базы и фото в
  `/var/backups/es12`, хранит 14 дней. Это тот же сервер, поэтому есть
  ещё недельная копия наружу — см. «Копии».

## Копии

| Что | Куда | Когда |
|---|---|---|
| База и фото | `/var/backups/es12` на сервере, 14 дней | каждую ночь, 03:30 |
| База (заявки, услуги, прайс, настройки) | почта `BACKUP_MAIL_TO`, файл `db-ДАТА.sql.gz.enc` | воскресенье, 04:30 |
| Фото и видео из админки | Яндекс Диск, папка `es12-kopii/foto` (только новые, с Диска не удаляются) | воскресенье, 04:30 |

Код, статьи и стартовые фото галереи лежат в GitHub — их копировать не нужно.

- Настройка (один раз, от root из `/opt/es12`): `deploy/offsite-setup.sh`.
  Спросит почту и пароль приложения Яндекса «Файлы (WebDAV)», проверит
  доступ к Диску, сгенерирует пароль для шифрования копий базы и покажет
  его один раз — владелец записывает его у себя. Всё хранится в `.env`:
  `BACKUP_MAIL_TO`, `BACKUP_ARCHIVE_PASSWORD`, `YANDEX_DISK_LOGIN`,
  `YANDEX_DISK_APP_PASSWORD`.
- Копия наружу сейчас: `deploy/offsite-backup.sh`. Журнал:
  `journalctl -u es12-offsite -n 50`.
- Восстановить базу из письма (пароль спросит):
  ```
  openssl enc -d -aes-256-cbc -pbkdf2 -iter 200000 -in db-ДАТА.sql.gz.enc -out db.sql.gz
  gunzip -c db.sql.gz | docker compose --env-file .env -f deploy/compose.yaml exec -T pgsql sh -c 'psql -U "$POSTGRES_USER" "$POSTGRES_DB"'
  ```
  Восстанавливать в пустую базу: на новом сервере — до первого
  `deploy/update.sh`, чтобы миграции не успели создать таблицы.
- Восстановить фото: скачать папку `es12-kopii/foto` с Диска и положить
  содержимое в `storage/app/public` контейнера `app`
  (`docker compose ... cp foto/. app:/var/www/html/storage/app/public/`).

## Первая установка (уже сделана, на случай нового сервера)

```
git clone https://github.com/promstroy407/electro-site.git /opt/es12
cd /opt/es12 && SITE_DOMAIN=es12.ru ACME_EMAIL=почта SITE_PHONE="+7 ..." SITE_EMAIL=почта deploy/install.sh
docker compose --env-file .env -f deploy/compose.yaml exec app php artisan admin:create почта
```

## Команды на сервере (из `/opt/es12`)

- Обновить сейчас, не дожидаясь таймера: `deploy/update.sh --force`
- Журнал последнего обновления: `journalctl -u es12-update -n 50`
- Журнал сайта: `docker compose --env-file .env -f deploy/compose.yaml logs --tail 100 app web`
- Новый администратор: `docker compose --env-file .env -f deploy/compose.yaml exec app php artisan admin:create почта`
- Копия сейчас: `deploy/backup.sh`

## Почта

Пока `MAIL_MAILER=log`: письма о заявках пишутся в журнал, а не
уходят. Чтобы отправлялись, в `/opt/es12/.env` нужны настройки SMTP
(`MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`,
`MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`). Пароль владелец вводит сам,
в чат и в git он не попадает. После правки: `deploy/update.sh --force`.

## Ссылки на мессенджеры

Ссылки «Telegram», «Канал в MAX» и «Группа ВКонтакте» появляются на
сайте, только когда в `/opt/es12/.env` задан адрес (`SITE_TELEGRAM` —
только `https://t.me/...`, `SITE_MAX_CHANNEL` — только `https://max.ru/...`,
`SITE_VK` — только `https://vk.ru/...` или `https://vk.com/...`; другие
адреса сайт не выводит). Задать или сменить (для ВК — `SITE_VK`):

```sh
cd /opt/es12 && source deploy/env-lib.sh && set_env SITE_MAX_CHANNEL "https://max.ru/..." && deploy/update.sh --force
```

Переходы на канал видны в Метрике, отчёт «Внешние
переходы» (счётчик собирает их сам, `trackLinks`).

## IndexNow — новые страницы сразу в Яндекс

После каждого обновления сайта `deploy/update.sh` сам сообщает Яндексу
о страницах из sitemap.xml, которых тот от нас ещё не получал
(`php artisan seo:indexnow`; отправленные адреса — в таблице
`indexnow_submissions`). Ключ `SITE_INDEXNOW_KEY` в `.env` создаётся
автоматически при первом обновлении; файл ключа — `https://es12.ru/<ключ>.txt`.
Сбой отправки обновление не ломает: адреса уйдут в следующий раз.
Проверить вручную, что ушло бы: `docker compose --env-file .env -f deploy/compose.yaml exec app php artisan seo:indexnow --dry-run`.
Услуга, добавленная в админке, уйдёт в Яндекс при ближайшем обновлении
сайта; если нужно сразу — запустить команду без `--dry-run`.

## Telegram-уведомления через пересыльщик

Если с сервера api.telegram.org не открывается, запросы бота можно
пустить через свой пересыльщик (HTTPS-адрес с секретным путём, который
передаёт запросы на api.telegram.org). В `/opt/es12/.env`:
`TELEGRAM_API_URL=https://адрес/секретный-путь` (вместе с
`TELEGRAM_BOT_TOKEN`), затем `deploy/update.sh --force`.
Пусто — напрямую на api.telegram.org. Секретный путь — секрет: только в
`.env`, в ошибках и журнале заменяется на `***`. Если пересыльщик
недоступен, заявки по-прежнему приходят на почту: каналы независимы.
Как устроен наш пересыльщик — во внутреннем документе проекта.

## Заявки в «Тендер-Радар»

Каждая заявка с сайта через очередь уходит в воронку сделок
«Тендер-Радара» (`POST https://tender.es12.ru/api/leads`, токен в
заголовке). В `/opt/es12/.env`: `TENDER_RADAR_LEADS_URL` и
`TENDER_RADAR_LEADS_TOKEN` (тот же, что `LEADS_API_TOKEN` на сервере
«Тендер-Радара»), затем `deploy/update.sh --force`. Пусто — не шлём.
До трёх попыток с паузой 1 и 5 минут; дубля не будет — «Тендер-Радар»
узнаёт заявку по `external_id` (`es12-<номер>`). Сбой пишется в журнал
(«Заявка не передана в Тендер-Радар»), почта и Telegram от него не
зависят. Токен в ошибках заменяется на `***`.
