<?php

declare(strict_types=1);

namespace App\Filament\Resources\GalleryPhotos\Pages;

use App\Enums\Audience;
use App\Enums\PhotoCategory;
use App\Filament\Resources\GalleryPhotos\GalleryPhotoResource;
use App\Filament\Resources\GalleryPhotos\Schemas\GalleryPhotoForm;
use App\Services\GalleryBulkUpload;
use App\Services\GalleryPhotos;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\Enum;

final class ListGalleryPhotos extends ListRecords
{
    /** Сколько фото за один раз: больше — дольше ждать загрузки. */
    public const BULK_MAX_FILES = 50;

    protected static string $resource = GalleryPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->bulkUploadAction(),
            CreateAction::make(),
        ];
    }

    /**
     * Несколько фото с одного объекта за раз. Права — те же, что на
     * добавление одного фото; логика — в сервисе GalleryBulkUpload.
     */
    private function bulkUploadAction(): Action
    {
        return Action::make('bulkUpload')
            ->label('Загрузить несколько')
            ->icon('heroicon-o-photo')
            ->color('gray')
            ->visible(fn (): bool => GalleryPhotoResource::canCreate())
            ->modalHeading('Несколько фото с одного объекта')
            ->modalDescription('Подпись берётся из имени файла: «03 Кабельная эстакада.jpg» → «Кабельная эстакада», номер в начале задаёт порядок. Подписи потом можно поправить у каждого фото.')
            ->modalSubmitActionLabel('Загрузить')
            ->schema([
                FileUpload::make('paths')
                    ->label('Фото')
                    ->multiple()
                    ->maxFiles(self::BULK_MAX_FILES)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(GalleryPhotoForm::MAX_FILE_KB)
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('1600')
                    ->disk(GalleryPhotos::DISK)
                    ->directory(GalleryPhotos::DIRECTORY)
                    ->visibility('public')
                    ->storeFileNamesIn('names')
                    ->required(),

                Select::make('category')
                    ->label('Раздел')
                    ->options(PhotoCategory::options())
                    ->rule(new Enum(PhotoCategory::class))
                    ->required(),

                TextInput::make('place')
                    ->label('Объект')
                    ->helperText('Общий для всех фото, без адреса: «Новый Уренгой», «Квартира, Йошкар-Ола».')
                    ->maxLength(100),

                Select::make('audience')
                    ->label('Для кого')
                    ->helperText('«Для дома» — фото квартир и домов: они же показываются в разделе «Для дома».')
                    ->options(Audience::options())
                    ->rule(new Enum(Audience::class))
                    ->default(Audience::Business->value)
                    ->required(),

                Toggle::make('is_published')
                    ->label('Показывать на сайте')
                    ->default(true),
            ])
            ->action(function (array $data, GalleryBulkUpload $upload): void {
                abort_unless(GalleryPhotoResource::canCreate(), 403);

                /** @var list<string> $paths */
                $paths = array_values((array) $data['paths']);
                /** @var array<string, string> $names */
                $names = (array) ($data['names'] ?? []);

                $count = $upload->store(
                    paths: $paths,
                    originalNames: $names,
                    category: PhotoCategory::from((string) $data['category']),
                    place: isset($data['place']) ? (string) $data['place'] : null,
                    published: (bool) ($data['is_published'] ?? true),
                    audience: Audience::from((string) ($data['audience'] ?? Audience::Business->value)),
                );

                Notification::make()->title('Добавлено фото: '.$count)->success()->send();
            });
    }
}
