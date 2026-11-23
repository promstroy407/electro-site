<?php

declare(strict_types=1);

namespace App\Filament\Resources\Services\Tables;

use App\Enums\Audience;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Родитель грузится сразу: ленивая загрузка вне прода запрещена.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('parent'))
            ->columns([
                TextColumn::make('title')
                    ->label('Название')
                    ->description(fn (Service $record): ?string => $record->parent?->title)
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('audience')
                    ->label('Для кого')
                    ->formatStateUsing(fn (Audience $state): string => $state->label())
                    ->badge(),
                IconColumn::make('is_published')
                    ->label('На сайте')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Изменена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Направление')
                    ->relationship(
                        'parent',
                        'title',
                        fn (Builder $query): Builder => $query->whereNull('parent_id'),
                    ),
                SelectFilter::make('audience')
                    ->label('Для кого')
                    ->options(Audience::options()),
                TernaryFilter::make('is_published')
                    ->label('На сайте'),
            ])
            ->recordActions([
                Action::make('open_on_site')
                    ->label('На сайте')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Service $record): string => route('services.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Service $record): bool => $record->is_published),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
