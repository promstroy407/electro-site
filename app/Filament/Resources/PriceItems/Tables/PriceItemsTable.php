<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceItems\Tables;

use App\Enums\PriceUnit;
use App\Models\PriceItem;
use App\Services\RubleFormatter;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class PriceItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Услуга грузится сразу: ленивая загрузка вне прода запрещена.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('service'))
            ->columns([
                TextColumn::make('title')
                    ->label('Работа')
                    ->description(fn (PriceItem $record): string => $record->service->title)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('price_kopecks')
                    ->label('Цена')
                    ->formatStateUsing(fn (PriceItem $record, RubleFormatter $rubles): string => ($record->is_from ? 'от ' : '')
                        .$rubles->format($record->price_kopecks))
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('За что')
                    ->formatStateUsing(fn (PriceUnit $state): string => $state->short()),
                IconColumn::make('is_published')
                    ->label('На сайте')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('service_id')
            ->filters([
                SelectFilter::make('service_id')
                    ->label('Услуга')
                    ->relationship('service', 'title')
                    ->searchable(),
                TernaryFilter::make('is_published')
                    ->label('На сайте'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
