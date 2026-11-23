<?php

declare(strict_types=1);

namespace App\Filament\Resources\CalculatorRates;

use App\Filament\Resources\CalculatorRates\Pages\CreateCalculatorRate;
use App\Filament\Resources\CalculatorRates\Pages\EditCalculatorRate;
use App\Filament\Resources\CalculatorRates\Pages\ListCalculatorRates;
use App\Filament\Resources\CalculatorRates\Schemas\CalculatorRateForm;
use App\Filament\Resources\CalculatorRates\Tables\CalculatorRatesTable;
use App\Models\CalculatorRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

// По образцу PriceItemResource. Доступ решает App\Policies\CalculatorRatePolicy.
final class CalculatorRateResource extends Resource
{
    protected static ?string $model = CalculatorRate::class;

    protected static ?string $slug = 'calculator';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 25;

    protected static ?string $navigationLabel = 'Калькулятор';

    protected static ?string $modelLabel = 'ставка калькулятора';

    protected static ?string $pluralModelLabel = 'ставки калькулятора';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CalculatorRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CalculatorRatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCalculatorRates::route('/'),
            'create' => CreateCalculatorRate::route('/create'),
            'edit' => EditCalculatorRate::route('/{record}/edit'),
        ];
    }
}
