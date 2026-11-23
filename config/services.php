<?php

declare(strict_types=1);

// Внешние сервисы. Значения — только из .env: токены это секреты.
// Остальные ключи (mailgun, ses и т. д.) Laravel берёт из своих умолчаний.

return [
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        // Пересыльщик до api.telegram.org (сервер в России): https://адрес/секретный-путь.
        'api_url' => env('TELEGRAM_API_URL', ''),
    ],

    // «Тендер-Радар» (tender.es12.ru): общая воронка сделок, туда уходят
    // заявки с сайта. Адрес — https://tender.es12.ru/api/leads, токен — тот
    // же, что LEADS_API_TOKEN на сервере «Тендер-Радара». Пусто — не шлём.
    'tender_radar' => [
        'leads_url' => env('TENDER_RADAR_LEADS_URL', ''),
        'leads_token' => env('TENDER_RADAR_LEADS_TOKEN', ''),
    ],

    // IndexNow: сайт сам сообщает Яндексу о новых страницах. Ключ не
    // секрет — по протоколу он открыто лежит на сайте (/<ключ>.txt);
    // на сервере его создаёт deploy/update.sh. Пусто — отправки нет.
    'indexnow' => [
        'key' => env('SITE_INDEXNOW_KEY', ''),
        'endpoint' => 'https://yandex.com/indexnow',
    ],
];
