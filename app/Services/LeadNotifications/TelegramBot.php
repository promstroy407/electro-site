<?php

declare(strict_types=1);

namespace App\Services\LeadNotifications;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

/**
 * Обращения к Telegram Bot API. Токен — секрет: живёт в .env
 * (TELEGRAM_BOT_TOKEN) и вырезается из любых текстов ошибок.
 *
 * С российского сервера api.telegram.org не открывается, поэтому
 * запросы можно пустить через пересыльщик владельца на его сервере за
 * границей: TELEGRAM_API_URL в .env, например
 * https://tg.example.ru/секретный-путь — он передаёт всё как есть на
 * api.telegram.org. Секретный путь в адресе — тоже секрет и тоже
 * вырезается из ошибок. Пусто — напрямую на api.telegram.org.
 */
final readonly class TelegramBot
{
    private const DEFAULT_API = 'https://api.telegram.org';

    public function __construct(
        private Http $http,
        private Config $config,
    ) {}

    public function hasToken(): bool
    {
        return $this->token() !== '';
    }

    public function sendMessage(string $chatId, string $text): void
    {
        $this->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => mb_substr($text, 0, 4096),
            'disable_web_page_preview' => true,
        ]);
    }

    /**
     * Последний чат, из которого писали боту: так владелец находит свой
     * ID чата, не разбираясь в API.
     *
     * @return array{id: string, title: string}|null
     */
    public function latestChat(): ?array
    {
        $updates = $this->call('getUpdates', ['limit' => 100])->json('result');

        if (! is_array($updates)) {
            return null;
        }

        foreach (array_reverse($updates) as $update) {
            $chat = is_array($update) ? $this->chatFromUpdate($update) : null;
            if ($chat !== null) {
                return $chat;
            }
        }

        return null;
    }

    /**
     * @param  array<mixed>  $update
     * @return array{id: string, title: string}|null
     */
    private function chatFromUpdate(array $update): ?array
    {
        foreach (['message', 'channel_post', 'my_chat_member'] as $key) {
            $entry = $update[$key] ?? null;
            $chat = is_array($entry) ? ($entry['chat'] ?? null) : null;

            if (! is_array($chat) || ! isset($chat['id']) || ! is_int($chat['id'])) {
                continue;
            }

            return [
                'id' => (string) $chat['id'],
                'title' => $this->chatTitle($chat),
            ];
        }

        return null;
    }

    /**
     * @param  array<mixed>  $chat
     */
    private function chatTitle(array $chat): string
    {
        $parts = [];
        foreach (['title', 'first_name', 'last_name'] as $key) {
            if (isset($chat[$key]) && is_string($chat[$key]) && $chat[$key] !== '') {
                $parts[] = $chat[$key];
            }
        }

        return $parts !== [] ? implode(' ', $parts) : 'без названия';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function call(string $method, array $payload): Response
    {
        $token = $this->token();
        if ($token === '') {
            throw new RuntimeException('Не задан токен Telegram-бота (TELEGRAM_BOT_TOKEN в .env).');
        }

        $api = $this->apiUrl();

        try {
            return $this->http
                ->timeout(10)
                ->asJson()
                ->post($api.'/bot'.$token.'/'.$method, $payload)
                ->throw();
        } catch (Throwable $e) {
            // Исходное исключение не прикрепляем: в нём адрес с токеном.
            $secrets = array_filter([$token, $api === self::DEFAULT_API ? '' : $api]);

            throw new RuntimeException('Telegram: '.str_replace($secrets, '***', $e->getMessage()));
        }
    }

    /**
     * Адрес Bot API: пересыльщик из .env (только https://, без «/» в конце)
     * или сам api.telegram.org.
     */
    private function apiUrl(): string
    {
        $url = $this->config->get('services.telegram.api_url');
        if (! is_string($url) || trim($url) === '') {
            return self::DEFAULT_API;
        }

        $url = rtrim(trim($url), '/');
        if (! str_starts_with($url, 'https://') || ! is_string(parse_url($url, PHP_URL_HOST))) {
            throw new RuntimeException('TELEGRAM_API_URL в .env записан неверно: нужен адрес вида https://tg.example.ru/секретный-путь.');
        }

        return $url;
    }

    private function token(): string
    {
        $token = $this->config->get('services.telegram.bot_token');

        return is_string($token) ? trim($token) : '';
    }
}
