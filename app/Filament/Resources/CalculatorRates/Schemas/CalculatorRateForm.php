<?php

declare(strict_types=1);

namespace App\Filament\Resources\CalculatorRates\Schemas;

use App\Rules\RublesAmount;
use App\Services\RubleFormatter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

// Ставка вводится в рублях за 1 м², хранится в копейках — как цена в прайсе.
final class CalculatorRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Работы')
                    ->placeholder('Например: Видеонаблюдение')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull(),

                TextInput::make('rate_kopecks')
                    ->label('Цена за 1 м², ₽')
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

                TextInput::make('sort_order')
                    ->label('Порядок')
                    ->helperText('Меньше — выше в списке.')
                    ->integer()
                    ->required()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(32767),

                Toggle::make('is_default')
                    ->label('Отмечена сразу')
                    ->helperText('Галочка стоит, когда посетитель открывает калькулятор.'),

                Toggle::make('is_published')
                    ->label('Показывать на сайте')
                    ->default(true),
            ]);
    }
}
