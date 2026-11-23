<?php

declare(strict_types=1);

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Services\ProjectPhotos;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

// Каждое поле валидируется по схеме таблицы projects.
final class ProjectForm
{
    public const MAX_PHOTOS = 20;

    /** Килобайты: 10 МБ — с запасом для фото с телефона. */
    public const MAX_PHOTO_KB = 10240;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Объект')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Название')
                        ->placeholder('Например: Склад 1200 м², пожарная сигнализация')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                            if ($operation === 'create' && blank($get('slug')) && filled($state)) {
                                $set('slug', Str::limit(Str::slug($state), 160, ''));
                            }
                        })
                        ->columnSpanFull(),

                    TextInput::make('slug')
                        ->label('Адрес страницы')
                        ->prefix('/obekty/')
                        ->helperText('Латиница, цифры и дефис. Подставляется из названия.')
                        ->required()
                        ->maxLength(160)
                        ->regex(ServiceForm::SLUG_PATTERN)
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    TextInput::make('city')
                        ->label('Город')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('year')
                        ->label('Год')
                        ->integer()
                        ->required()
                        ->default(fn (): int => (int) now()->format('Y'))
                        ->minValue(2000)
                        ->maxValue(fn (): int => (int) now()->format('Y') + 1),

                    Select::make('services')
                        ->label('Выполненные работы')
                        ->helperText('По ним объект попадёт на страницы услуг и в фильтр.')
                        ->relationship('services', 'title')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->columnSpanFull(),

                    Toggle::make('is_published')
                        ->label('Показывать на сайте')
                        ->default(false),

                    TextInput::make('sort_order')
                        ->label('Порядок')
                        ->helperText('Внутри одного года: меньше — выше.')
                        ->integer()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(32767),
                ]),

            Section::make('Фото')
                ->description('Первое фото — обложка. Перетаскивайте, чтобы поменять порядок.')
                ->schema([
                    FileUpload::make('photos')
                        ->hiddenLabel()
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->maxFiles(self::MAX_PHOTOS)
                        ->maxSize(self::MAX_PHOTO_KB)
                        ->disk(ProjectPhotos::DISK)
                        ->directory(ProjectPhotos::DIRECTORY)
                        ->visibility('public')
                        ->panelLayout('grid'),
                ]),

            Section::make('Описание')
                ->schema([
                    Textarea::make('summary')
                        ->label('Кратко')
                        ->helperText('Одна-две фразы для карточки.')
                        ->rows(2)
                        ->maxLength(300),

                    MarkdownEditor::make('body')
                        ->label('Подробно')
                        ->helperText('Что было на объекте, что сделали, сроки.')
                        ->maxLength(20000)
                        ->disableToolbarButtons(['attachFiles']),
                ]),
        ]);
    }
}
