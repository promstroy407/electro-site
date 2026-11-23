<?php

declare(strict_types=1);

namespace App\Filament\Resources\GalleryPhotos\Tables;

use App\Enums\Audience;
use App\Enums\MediaKind;
use App\Enums\PhotoCategory;
use App\Filament\Resources\GalleryPhotos\GalleryPhotoResource;
use App\Models\GalleryPhoto;
use App\Services\GalleryPhotoAudience;
use App\Services\GalleryPhotos;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rules\Enum;

final class GalleryPhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview')
                    ->label('Фото')
                    ->state(fn (GalleryPhoto $record): ?string => $record->previewPath())
                    ->disk(GalleryPhotos::DISK)
                    ->square(),
                TextColumn::make('caption')
                    ->label('Подпись')
                    ->description(fn (GalleryPhoto $record): ?string => $record->place)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('kind')
                    ->label('Вид')
                    ->formatStateUsing(fn (MediaKind $state): string => $state->label())
                    ->badge(),
                TextColumn::make('category')
                    ->label('Раздел')
                    ->formatStateUsing(fn (PhotoCategory $state): string => $state->label())
                    ->badge(),
                TextColumn::make('audience')
                    ->label('Для кого')
                    ->formatStateUsing(fn (Audience $state): string => $state->label())
                    ->badge(),
                IconColumn::make('is_published')
                    ->label('На сайте')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('category')->label('Раздел')->options(PhotoCategory::options()),
                SelectFilter::make('audience')->label('Для кого')->options(Audience::options()),
                TernaryFilter::make('is_published')->label('На сайте'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                // Несколько отмеченных фото — сразу «для дома» или «для бизнеса».
                BulkAction::make('setAudience')
                    ->label('Для кого')
                    ->icon('heroicon-o-user-group')
                    ->authorize(fn (): bool => GalleryPhotoResource::canCreate())
                    ->schema([
                        Select::make('audience')
                            ->label('Для кого')
                            ->options(Audience::options())
                            ->rule(new Enum(Audience::class))
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data, GalleryPhotoAudience $service): void {
                        $service->assign($records, Audience::from((string) $data['audience']));
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
