<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceItems\Pages;

use App\Filament\Resources\PriceItems\PriceItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePriceItem extends CreateRecord
{
    protected static string $resource = PriceItemResource::class;
}
