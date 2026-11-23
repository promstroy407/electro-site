<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadOrigin;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Услуга грузится сразу: ленивая загрузка вне прода запрещена.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('service'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Поступила')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->url(fn (Lead $record): string => 'tel:'.$record->phone),
                TextColumn::make('organization')
                    ->label('Организация')
                    ->placeholder('—')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('service.title')
                    ->label('Услуга')
                    ->placeholder('—')
                    ->wrap(),
                TextColumn::make('origin')
                    ->label('Откуда узнал')
                    ->formatStateUsing(fn (?LeadOrigin $state): string => $state?->label() ?? '—')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (LeadStatus $state): string => $state->label())
                    ->color(fn (LeadStatus $state): string => $state->color())
                    ->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(LeadStatus::options()),
                SelectFilter::make('origin')
                    ->label('Откуда узнал')
                    ->options(LeadOrigin::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
