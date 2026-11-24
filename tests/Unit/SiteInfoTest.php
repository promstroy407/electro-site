<?php

declare(strict_types=1);

use App\Support\SiteInfo;

it('собирается из конфига и не падает на пустых и нестроковых значениях', function (): void {
    $site = SiteInfo::fromConfig([
        'company_name' => 'Тест',
        'city' => 'Йошкар-Ола',
        'phone' => null,
        'email' => 42,
    ]);

    expect($site->companyName)->toBe('Тест')
        ->and($site->city)->toBe('Йошкар-Ола')
        ->and($site->region)->toBe('')
        ->and($site->phone)->toBe('')
        ->and($site->email)->toBe('');
});

it('готовит телефон для ссылки tel:', function (string $phone, string $expected): void {
    $site = SiteInfo::fromConfig(['phone' => $phone]);

    expect($site->phoneHref())->toBe($expected);
})->with([
    'с плюсом и скобками' => ['+7 (8362) 12-34-56', '+78362123456'],
    'с восьмёркой' => ['8 800 555-35-35', '88005553535'],
    'пустой' => ['', ''],
]);

it('берёт в ссылку Telegram только адреса t.me', function (string $url, string $expected): void {
    expect(SiteInfo::fromConfig(['telegram' => $url])->telegram)->toBe($expected);
})->with([
    'ссылка t.me' => ['https://t.me/+79371124897', 'https://t.me/+79371124897'],
    'чужой сайт' => ['https://example.com/t.me', ''],
    'javascript' => ['javascript:alert(1)', ''],
    'пусто' => ['', ''],
]);

it('берёт в ссылку на канал MAX только адреса max.ru', function (string $url, string $expected): void {
    expect(SiteInfo::fromConfig(['max_channel' => $url])->maxChannel)->toBe($expected);
})->with([
    'ссылка max.ru' => ['https://max.ru/join/abc123', 'https://max.ru/join/abc123'],
    'голый адрес без канала' => ['https://max.ru/', ''],
    'чужой сайт' => ['https://max.ru.example.com/x', ''],
    'без https' => ['http://max.ru/join/abc123', ''],
    'javascript' => ['javascript:alert(1)', ''],
    'пусто' => ['', ''],
]);

it('берёт в ссылку на группу ВК только адреса vk.ru и vk.com', function (string $url, string $expected): void {
    expect(SiteInfo::fromConfig(['vk' => $url])->vk)->toBe($expected);
})->with([
    'vk.ru' => ['https://vk.ru/elektrostroymariel', 'https://vk.ru/elektrostroymariel'],
    'vk.com' => ['https://vk.com/elektrostroymariel', 'https://vk.com/elektrostroymariel'],
    'голый адрес' => ['https://vk.ru/', ''],
    'чужой сайт' => ['https://vk.ru.example.com/x', ''],
    'пусто' => ['', ''],
]);

it('собирает канал и группу в список, пропуская пустые', function (): void {
    $site = SiteInfo::fromConfig(['max_channel' => '', 'vk' => 'https://vk.ru/gruppa']);

    expect($site->socialLinks())->toBe([['label' => 'Группа ВКонтакте', 'url' => 'https://vk.ru/gruppa']]);
});

it('берёт номер счётчика Метрики только из цифр', function (string $value, string $expected): void {
    expect(SiteInfo::fromConfig(['metrika_id' => $value])->metrikaId)->toBe($expected);
})->with([
    'номер' => ['114072347', '114072347'],
    'с кодом' => ['1);alert(1', ''],
    'пусто' => ['', ''],
]);

it('собирает реквизиты ИП для подвала и политики', function (): void {
    $site = SiteInfo::fromConfig([
        'legal_name' => 'ИП Иванов Иван Иванович',
        'inn' => '123456789012',
        'ogrnip' => '312345678901234',
    ]);

    expect($site->requisites())->toBe('ИП Иванов Иван Иванович, ИНН 123456789012, ОГРНИП 312345678901234');
});

it('без названия реквизиты не показывает', function (): void {
    expect(SiteInfo::fromConfig(['inn' => '123456789012'])->requisites())->toBe('');
});

it('пропускает ИНН и ОГРНИП только нужной длины из цифр', function (string $inn, string $ogrnip, string $expected): void {
    $site = SiteInfo::fromConfig(['legal_name' => 'ИП Тест', 'inn' => $inn, 'ogrnip' => $ogrnip]);

    expect($site->requisites())->toBe($expected);
})->with([
    'ИНН юрлица и ОГРН' => ['1234567890', '1234567890123', 'ИП Тест, ИНН 1234567890, ОГРН 1234567890123'],
    'буквы' => ['12345678901a', '31234567890123x', 'ИП Тест'],
    'не та длина' => ['123', '3123', 'ИП Тест'],
    'пробелы по краям' => [' 123456789012 ', '312345678901234', 'ИП Тест, ИНН 123456789012, ОГРНИП 312345678901234'],
]);
