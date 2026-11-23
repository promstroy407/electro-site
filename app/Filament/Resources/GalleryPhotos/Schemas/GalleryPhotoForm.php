<?php

declare(strict_types=1);

namespace App\Filament\Resources\GalleryPhotos\Schemas;

use App\Enums\Audience;
use App\Enums\PhotoCategory;
use App\Services\GalleryPhotos;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Enum;

// Каждое поле валидируется по схеме таблицы gallery_photos.
final class GalleryPhotoForm
{
    /** Килобайты: 100 МБ — с запасом для видео с телефона. */
    public const MAX_FILE_KB = 102400;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Фото или видео')
                ->columns(2)
                ->schema([
                    FileUpload::make('path')
                        ->label('Файл')
                        ->helperText('Фото (JPEG, PNG, WebP) или видео (MP4, WebM), до 100 МБ.')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm'])
                        ->maxSize(self::MAX_FILE_KB)
                        // Большие фото с телефона уменьшаются ещё в браузере.
                        ->imageResizeMode('contain')
                        ->imageResizeTargetWidth('1600')
                        ->imageResizeTargetHeight('1600')
                        ->disk(GalleryPhotos::DISK)
                        ->directory(GalleryPhotos::DIRECTORY)
                        ->visibility('public')
                        ->required()
                        ->columnSpanFull(),

                    FileUpload::make('poster_path')
                        ->label('Обложка видео')
                        ->helperText('Только для видео, необязательно. Без обложки покажем первый кадр.')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(10240)
                        ->disk(GalleryPhotos::DISK)
                        ->directory(GalleryPhotos::DIRECTORY)
                        ->visibility('public')
                        ->columnSpanFull(),

                    TextInput::make('caption')
                        ->label('Подпись')
                        ->placeholder('Например: ВРУ с автоматическим вводом резерва')
                        ->required()
                        ->maxLength(200)
                        ->columnSpanFull(),

                    Select::make('category')
                        ->label('Раздел')
                        ->options(PhotoCategory::options())
                        ->rule(new Enum(PhotoCategory::class))
                        ->required(),

                    TextInput::make('place')
                        ->label('Объект')
                        ->helperText('Где снято, без адреса: «Торговый центр». Можно оставить пустым.')
                        ->maxLength(100),

                    Select::make('audience')
                        ->label('Для кого')
                        ->helperText('«Для дома» — фото квартир и домов, показываются в разделе «Для дома».')
                        ->options(Audience::options())
                        ->rule(new Enum(Audience::class))
                        ->default(Audience::Business->value)
                        ->required(),

                    Toggle::make('is_published')
                        ->label('Показывать на сайте')
                        ->default(true),

                    TextInput::make('sort_order')
                        ->label('Порядок')
                        ->helperText('Меньше — выше.')
                        ->integer()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(32767),
                ]),
        ]);
    }
}
