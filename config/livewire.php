<?php

declare(strict_types=1);

// Только то, что отличается от настроек Livewire по умолчанию: загрузка
// файлов в админке чуть больше 100 МБ (видео в галерею; точный предел
// в 100 МБ проверяет сама форма). Остальное — из пакета.
return [
    'temporary_file_upload' => [
        'disk' => null,
        'rules' => ['required', 'file', 'max:110000'],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4', 'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 10,
        'cleanup' => true,
    ],
];
