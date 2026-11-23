<?php

declare(strict_types=1);

namespace App\Filament\Resources\CalculatorRates\Pages;

use App\Filament\Resources\CalculatorRates\CalculatorRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListCalculatorRates extends ListRecords
{
    protected static string $resource = CalculatorRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
