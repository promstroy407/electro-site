<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceItems\Schemas;

use App\Enums\PriceUnit;
use App\Rules\RublesAmount;
use App\Services\RubleFormatter;
use App\Services\ServiceTree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Enum;

// Цена вводится в рублях («1500» или «1500,50»), хранится в копейках.
// Перевод — только через RubleFormatter: без float, без округлений.
final class PriceItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('service_id')
                    ->label('Услуга')
                    ->options(fn (ServiceTree $tree): array => $tree->groupedOptions())
                    ->required()
                    ->rules(['integer', 'exists:services,id'])
                    ->searchable()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Работа')
                    ->placeholder('Например: Монтаж IP-камеры')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),

                TextInput::make('price_kopecks')
                    ->label('Цена, ₽')
                    ->helperText('Например: 1500 или 1500,50.')
                    ->required()
                    ->maxLength(16)
                    ->rules([fn (RubleFormatter $rubles): RublesAmount => new RublesAmount($rubles)])
                    ->formatStateUsing(fn (mixed $state, RubleFormatter $rubles): ?string => is_numeric($state)
                        ? $rubles->toInput((int) $state)
                        : null)
                    ->dehydrateStateUsing(fn (mixed $state, RubleFormatter $rubles): ?int => is_string($state)
                        ? $rubles->parse($state)
                        : null),

                Select::make('unit')
                    ->label('За что')
                    ->options(PriceUnit::options())
                    ->default(PriceUnit::Piece->value)
                    ->required()
                    ->rule(new Enum(PriceUnit::class))
                    ->native(false),

                Toggle::make('is_from')
                    ->label('Цена «от»')
                    ->helperText('Показывать «от 1 500 ₽», если итог зависит от объекта.')
                    ->default(true),

                Toggle::make('is_published')
                    ->label('Показывать на сайте')
                    ->default(true),

                TextInput::make('sort_order')
                    ->label('Порядок')
                    ->helperText('Меньше — выше в списке.')
                    ->integer()
                    ->required()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(32767),
            ]);
    }
}
