<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationSettings\Schemas;

use App\Services\LeadNotifications\TelegramBot;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class NotificationSettingsForm
{
    public const CHAT_ID_PATTERN = '/^(-?\d{1,20}|@[A-Za-z0-9_]{5,32})$/';

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Почта')
                ->schema([
                    Toggle::make('mail_enabled')
                        ->label('Отправлять заявки на почту')
                        ->live(),
                    TagsInput::make('emails')
                        ->label('Адреса')
                        ->placeholder('Введите адрес и нажмите Enter')
                        ->helperText('Можно несколько: например, ваш и менеджера. Не больше 10.')
                        ->splitKeys(['Tab', ',', ' '])
                        ->required(fn (Get $get): bool => (bool) $get('mail_enabled'))
                        ->rules(['array', 'max:10'])
                        ->nestedRecursiveRules(['email:rfc', 'max:255']),
                ]),

            Section::make('Telegram')
                ->schema([
                    Toggle::make('telegram_enabled')
                        ->label('Отправлять заявки в Telegram')
                        ->live(),
                    TextInput::make('telegram_chat_id')
                        ->label('Чат')
                        ->helperText(fn (TelegramBot $bot): string => 'Числовой ID чата или @имя канала. Проще всего: напишите боту любое сообщение '
                            .'и нажмите вверху «Найти мой чат». Токен бота задаётся в .env (TELEGRAM_BOT_TOKEN), сейчас он '
                            .($bot->hasToken() ? 'задан.' : 'не задан — Telegram работать не будет.'))
                        ->required(fn (Get $get): bool => (bool) $get('telegram_enabled'))
                        ->maxLength(64)
                        ->regex(self::CHAT_ID_PATTERN),
                ]),
        ]);
    }
}
