<?php

declare(strict_types=1);

// Данные компании, которые видит посетитель. Значения — из .env,
// чтобы смена названия или телефона не требовала правки кода.

return [
    'company_name' => env('SITE_COMPANY_NAME', 'ЭлектроСтрой Марий Эл'),
    'city' => env('SITE_CITY', 'Йошкар-Ола'),
    // Город в предложном падеже: «базируемся в Йошкар-Оле».
    'city_in' => env('SITE_CITY_IN', 'Йошкар-Оле'),
    'region' => env('SITE_REGION', 'Республика Марий Эл'),
    'address' => env('SITE_ADDRESS', ''),
    'phone' => env('SITE_PHONE', ''),
    'email' => env('SITE_EMAIL', ''),
    'work_hours' => env('SITE_WORK_HOURS', ''),
    // Ссылка на Telegram для связи: https://t.me/... Пусто — кнопки нет.
    'telegram' => env('SITE_TELEGRAM', ''),
    // Ссылка на канал в MAX: https://max.ru/... Пусто — ссылок на канал нет.
    'max_channel' => env('SITE_MAX_CHANNEL', ''),
    // Группа ВКонтакте: https://vk.ru/... или https://vk.com/... Пусто — ссылок нет.
    'vk' => env('SITE_VK', ''),
    // Номер счётчика Яндекс Метрики (только цифры). Пусто — счётчика на сайте нет.
    'metrika_id' => env('YANDEX_METRIKA_ID', ''),
    // Начальный адрес для заявок: подставляется сидером, если в админке пусто.
    'notify_email' => env('SITE_NOTIFY_EMAIL', ''),
];
