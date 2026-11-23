<?php

declare(strict_types=1);

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use App\Services\ProjectPhotos;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Метки грузятся сразу: ленивая загрузка вне прода запрещена.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('services'))
            ->columns([
                ImageColumn::make('cover')
                    ->label('Фото')
                    ->state(fn (Project $record): ?string => $record->coverPath())
                    ->disk(ProjectPhotos::DISK)
                    ->square(),
                TextColumn::make('title')
                    ->label('Объект')
                    ->description(fn (Project $record): string => $record->city.', '.$record->year)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('services.title')
                    ->label('Работы')
                    ->badge()
                    ->limitList(3),
                IconColumn::make('is_published')
                    ->label('На сайте')
                    ->boolean(),
                TextColumn::make('year')
                    ->label('Год')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('year', 'desc')
            ->filters([
                TernaryFilter::make('is_published')->label('На сайте'),
            ])
            ->recordActions([
                Action::make('open_on_site')
                    ->label('На сайте')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Project $record): string => route('projects.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Project $record): bool => $record->is_published),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
