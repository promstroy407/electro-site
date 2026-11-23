<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceItems\Pages;

use App\Filament\Resources\PriceItems\PriceItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListPriceItems extends ListRecords
{
    protected static string $resource = PriceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
