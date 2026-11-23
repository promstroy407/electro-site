<?php

declare(strict_types=1);

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\Audience;
use App\Enums\ServiceIcon;
use App\Models\Service;
use App\Rules\TwoLevelParent;
use App\Services\ServiceTree;
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
use Illuminate\Validation\Rules\Enum;

// Каждое поле валидируется по схеме таблицы services:
// длина строки = размер колонки, числа в границах типа, enum — списком.
final class ServiceForm
{
    public const SLUG_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Название')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                            // Адрес подставляется из названия только у новой услуги:
                            // у опубликованной он уже мог попасть в поиск и ссылки.
                            if ($operation === 'create' && blank($get('slug')) && filled($state)) {
                                $set('slug', Str::limit(Str::slug($state), 160, ''));
                            }
                        })
                        ->columnSpanFull(),

                    TextInput::make('slug')
                        ->label('Адрес страницы')
                        ->prefix('/uslugi/')
                        ->helperText('Латиница, цифры и дефис. Подставляется из названия.')
                        ->required()
                        ->maxLength(160)
                        ->regex(self::SLUG_PATTERN)
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    Select::make('parent_id')
                        ->label('Направление')
                        ->helperText('Пусто — это само направление (верхний уровень).')
                        ->placeholder('— верхний уровень —')
                        ->options(fn (?Service $record, ServiceTree $tree): array => $tree->parentOptions($record))
                        ->rules([fn (?Service $record): TwoLevelParent => new TwoLevelParent($record)])
                        ->native(false),

                    Select::make('audience')
                        ->label('Для кого')
                        ->options(Audience::options())
                        ->default(Audience::All->value)
                        ->required()
                        ->rule(new Enum(Audience::class))
                        ->native(false),

                    Select::make('icon')
                        ->label('Картинка на сайте')
                        ->helperText('Иконка на карточке направления. Пусто — общая.')
                        ->options(ServiceIcon::options())
                        ->placeholder('— общая —')
                        ->rule(new Enum(ServiceIcon::class))
                        ->native(false),

                    TextInput::make('sort_order')
                        ->label('Порядок')
                        ->helperText('Меньше — выше в списке.')
                        ->integer()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(32767),

                    Toggle::make('is_published')
                        ->label('Опубликована на сайте')
                        ->default(false)
                        ->inline(false),
                ]),

            Section::make('Описание')
                ->schema([
                    Textarea::make('summary')
                        ->label('Кратко')
                        ->helperText('Одна-две фразы для карточки в списке услуг.')
                        ->rows(2)
                        ->maxLength(300),

                    MarkdownEditor::make('body')
                        ->label('Подробное описание')
                        ->maxLength(20000)
                        ->disableToolbarButtons(['attachFiles']),
                ]),

            Section::make('Поисковики')
                ->description('Если пусто — берутся название и краткое описание.')
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Заголовок для поисковиков')
                        ->maxLength(70),

                    Textarea::make('meta_description')
                        ->label('Описание для поисковиков')
                        ->rows(2)
                        ->maxLength(160),
                ]),
        ]);
    }
}
