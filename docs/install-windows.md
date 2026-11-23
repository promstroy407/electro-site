# Запуск проекта на Windows

Проект работает в Docker внутри WSL2 (Linux в Windows). Ставится один раз,
дальше сайт поднимается одной командой.

Нужно: Windows 10 (версия 2004 и новее) или Windows 11, права администратора,
около 10 ГБ свободного места.

## 1. WSL2 и Ubuntu

1. Откройте **PowerShell от имени администратора** (Пуск → правой кнопкой по
   «Терминал» или «PowerShell» → «Запуск от имени администратора»).
2. Выполните:
   ```
   wsl --install -d Ubuntu
   ```
3. Перезагрузите компьютер.
4. После перезагрузки откроется окно Ubuntu: придумайте имя пользователя
   и пароль Linux (пароль при вводе не отображается — это нормально).

Дальше все команды — **в окне Ubuntu** (Пуск → Ubuntu), не в PowerShell.

## 2. Docker Desktop

1. Скачайте Docker Desktop для Windows: https://www.docker.com/products/docker-desktop/
2. При установке оставьте галочку **Use WSL 2 instead of Hyper-V**.
3. Запустите Docker Desktop, дождитесь зелёного статуса «Engine running».
4. Docker Desktop → Settings → Resources → **WSL integration** → включите
   переключатель у **Ubuntu** → Apply & restart.
5. Проверьте в окне Ubuntu:
   ```
   docker run --rm hello-world
   ```
   Должно появиться «Hello from Docker!».

## 3. Git и утилиты

В окне Ubuntu:
```
sudo apt update && sudo apt install -y git openssl
git config --global user.name "Ваше имя"
git config --global user.email "ваша@почта"
```

## 4. Распаковать проект

Держите проект внутри Linux (в домашней папке Ubuntu), а не на диске C:
так Docker работает в разы быстрее.

Архив скачан в «Загрузки» Windows. В окне Ubuntu (замените `ИМЯ` на имя
вашей учётной записи Windows):
```
mkdir -p ~/projects && cd ~/projects
tar xzf /mnt/c/Users/ИМЯ/Downloads/electro-site.tar.gz
cd electro-site
```

## 5. Первый запуск

```
bin/setup
```

Первый раз займёт 5–15 минут: скачиваются образы и зависимости.
В конце скрипт напечатает **логин и пароль админки — сохраните их**, они
показываются один раз.

Откройте в браузере Windows:
- сайт: http://localhost
- админка: http://localhost/admin

Если порт 80 занят (ошибка `port is already allocated`): откройте `.env`,
поставьте `APP_PORT=8080` и повторите `bin/setup`. Адрес станет
http://localhost:8080.

## 6. Проверка гейтов и первый коммит после установки

```
./vendor/bin/sail composer check
```

Это формат → статанализ → тесты. **Пришлите мне весь вывод этой команды**,
даже если он зелёный: код писался без возможности запуска, я поправлю
найденное.

Когда всё зелёное, зафиксируйте версии зависимостей:
```
git add composer.lock package-lock.json
git commit -m "Зафиксированы версии зависимостей после первой установки"
```

## Каждый день

```
cd ~/projects/electro-site
./vendor/bin/sail up -d       # поднять
./vendor/bin/sail stop        # остановить
./vendor/bin/sail composer check   # гейты перед коммитом
./vendor/bin/sail artisan migrate:fresh --seed   # пересоздать БД с демо-данными (новый пароль админки)
```

Редактировать код удобно в VS Code с расширением **WSL**: в папке проекта
выполните `code .`.

## Если что-то пошло не так

- `docker: command not found` в Ubuntu — не включена WSL integration (шаг 2.4).
- `Cannot connect to the Docker daemon` — не запущен Docker Desktop.
- Забыли пароль админки — `./vendor/bin/sail artisan migrate:fresh --seed`
  (база пересоздаётся, демо-данные заливаются заново, печатается новый пароль).
- Любая другая ошибка — пришлите текст целиком.
