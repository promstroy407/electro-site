<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\Schemas;

use App\Services\PartnerLogos;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

// Каждое поле валидируется по схеме таблицы partners.
final class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Партнёр')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->placeholder('Например: СК М ГРУПП')
                        ->required()
                        ->maxLength(100)
                        ->columnSpanFull(),

                    TextInput::make('url')
                        ->label('Сайт')
                        ->placeholder('https://example.ru')
                        ->helperText('Полный адрес с https://. Можно оставить пустым.')
                        ->url()
                        ->rule('starts_with:http://,https://')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Коротко о партнёре')
                        ->placeholder('Например: строительная компания, Йошкар-Ола')
                        ->rows(2)
                        ->maxLength(300)
                        ->columnSpanFull(),

                    FileUpload::make('logo_path')
                        ->label('Логотип')
                        ->helperText('PNG, JPEG, WebP или SVG, до 2 МБ. Без логотипа покажем название.')
                        ->image()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                        ->maxSize(2048)
                        ->disk(PartnerLogos::DISK)
                        ->directory(PartnerLogos::DIRECTORY)
                        ->visibility('public')
                        ->columnSpanFull(),

                    Toggle::make('is_published')
                        ->label('Показывать на сайте')
                        ->default(true),

                    TextInput::make('sort_order')
                        ->label('Порядок')
                        ->helperText('Меньше — выше.')
                        ->integer()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(32767),
                ]),
        ]);
    }
}
