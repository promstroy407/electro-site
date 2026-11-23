<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class NotificationSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('mail_enabled')->label('Почта')->boolean(),
                TextColumn::make('emails')->label('Адреса')->badge()->placeholder('не заданы'),
                IconColumn::make('telegram_enabled')->label('Telegram')->boolean(),
                TextColumn::make('telegram_chat_id')->label('Чат')->placeholder('не задан'),
            ])
            ->paginated(false)
            ->recordActions([
                EditAction::make()->label('Изменить'),
            ]);
    }
}
