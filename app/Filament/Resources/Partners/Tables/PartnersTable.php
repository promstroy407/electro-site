<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\Tables;

use App\Models\Partner;
use App\Services\PartnerLogos;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Логотип')
                    ->disk(PartnerLogos::DISK),
                TextColumn::make('name')
                    ->label('Название')
                    ->description(fn (Partner $record): ?string => $record->description)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('url')
                    ->label('Сайт')
                    ->limit(40),
                IconColumn::make('is_published')
                    ->label('На сайте')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_published')->label('На сайте'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
