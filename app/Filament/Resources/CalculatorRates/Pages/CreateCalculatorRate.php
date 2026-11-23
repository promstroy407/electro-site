<?php

declare(strict_types=1);

namespace App\Filament\Resources\CalculatorRates\Pages;

use App\Filament\Resources\CalculatorRates\CalculatorRateResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCalculatorRate extends CreateRecord
{
    protected static string $resource = CalculatorRateResource::class;
}
