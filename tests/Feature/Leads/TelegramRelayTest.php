<?php

declare(strict_types=1);

use App\Services\LeadNotifications\TelegramBot;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('services.telegram.bot_token', '123:SECRET');
});

it('шлёт запросы к Telegram через пересыльщик из .env', function (): void {
    config()->set('services.telegram.api_url', 'https://tg.example.test/s3cr3t/');
    Http::fake(['*' => Http::response(['ok' => true])]);

    app(TelegramBot::class)->sendMessage('555', 'Новая заявка');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://tg.example.test/s3cr3t/bot123:SECRET/sendMessage');
});

it('без пересыльщика ходит прямо на api.telegram.org', function (): void {
    config()->set('services.telegram.api_url', '');
    Http::fake(['*' => Http::response(['ok' => true])]);

    app(TelegramBot::class)->sendMessage('555', 'Новая заявка');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.telegram.org/bot123:SECRET/sendMessage');
});

it('не пишет секретный путь и токен в текст ошибки', function (): void {
    config()->set('services.telegram.api_url', 'https://tg.example.test/s3cr3t');
    Http::fake(['*' => Http::failedConnection('Could not resolve https://tg.example.test/s3cr3t/bot123:SECRET/sendMessage')]);

    expect(fn () => app(TelegramBot::class)->sendMessage('555', 'Новая заявка'))
        ->toThrow(RuntimeException::class, 'Telegram: Could not resolve ***/bot***/sendMessage');
});

it('сообщает понятной ошибкой о неверно записанном адресе', function (string $url): void {
    config()->set('services.telegram.api_url', $url);
    Http::fake();

    expect(fn () => app(TelegramBot::class)->sendMessage('555', 'Новая заявка'))
        ->toThrow(RuntimeException::class, 'TELEGRAM_API_URL в .env записан неверно');
    Http::assertNothingSent();
})->with([
    'без https' => ['http://tg.example.test/s3cr3t'],
    'не адрес' => ['tg.example.test'],
]);
