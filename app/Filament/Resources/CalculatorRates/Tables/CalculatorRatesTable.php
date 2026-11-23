<?php

declare(strict_types=1);

namespace App\Filament\Resources\CalculatorRates\Tables;

use App\Models\CalculatorRate;
use App\Services\RubleFormatter;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CalculatorRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Работы')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('rate_kopecks')
                    ->label('За 1 м²')
                    ->formatStateUsing(fn (CalculatorRate $record, RubleFormatter $rubles): string => $rubles->format($record->rate_kopecks))
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label('Отмечена сразу')
                    ->boolean(),
                IconColumn::make('is_published')
                    ->label('На сайте')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
