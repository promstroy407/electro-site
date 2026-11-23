<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceItems;

use App\Filament\Resources\PriceItems\Pages\CreatePriceItem;
use App\Filament\Resources\PriceItems\Pages\EditPriceItem;
use App\Filament\Resources\PriceItems\Pages\ListPriceItems;
use App\Filament\Resources\PriceItems\Schemas\PriceItemForm;
use App\Filament\Resources\PriceItems\Tables\PriceItemsTable;
use App\Models\PriceItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

// По образцу ServiceResource. Доступ решает App\Policies\PriceItemPolicy.
final class PriceItemResource extends Resource
{
    protected static ?string $model = PriceItem::class;

    protected static ?string $slug = 'prices';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Прайс';

    protected static ?string $modelLabel = 'позиция прайса';

    protected static ?string $pluralModelLabel = 'прайс';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PriceItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceItems::route('/'),
            'create' => CreatePriceItem::route('/create'),
            'edit' => EditPriceItem::route('/{record}/edit'),
        ];
    }
}
