<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Enums\PhotoCategory;
use App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos;
use App\Models\GalleryPhoto;
use App\Models\User;
use App\Services\GalleryBulkUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    Storage::fake('public');
});

it('берёт подпись из имени файла', function (string $fileName, string $caption): void {
    expect(app(GalleryBulkUpload::class)->captionFromFileName($fileName))->toBe($caption);
})->with([
    'номер и пробел' => ['03 Кабельная эстакада.jpg', 'Кабельная эстакада'],
    'номер и подчёркивания' => ['12_щит_в_квартире.JPG', 'Щит в квартире'],
    'без номера' => ['Свет на участке.webp', 'Свет на участке'],
    'имя камеры' => ['IMG_6797.JPG', GalleryBulkUpload::DEFAULT_CAPTION],
    'только номер' => ['07.jpg', GalleryBulkUpload::DEFAULT_CAPTION],
]);

it('ставит порядок по номеру в начале имени', function (): void {
    $upload = app(GalleryBulkUpload::class);

    expect($upload->orderFromFileName('03 Эстакада.jpg'))->toBe(3)
        ->and($upload->orderFromFileName('Эстакада.jpg'))->toBe(0);
});

it('загружает несколько фото с общим разделом и объектом', function (): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(ListGalleryPhotos::class)
        ->callAction('bulkUpload', data: [
            'paths' => [
                UploadedFile::fake()->image('01 Кабельная эстакада.jpg', 1200, 900),
                UploadedFile::fake()->image('02 Лотки в цехе.jpg', 1200, 900),
            ],
            'category' => PhotoCategory::Cables->value,
            'place' => 'Новый Уренгой',
            'audience' => Audience::Business->value,
            'is_published' => true,
        ])
        ->assertHasNoActionErrors();

    $photos = GalleryPhoto::query()->orderBy('sort_order')->get();

    expect($photos)->toHaveCount(2)
        ->and($photos->pluck('caption')->all())->toBe(['Кабельная эстакада', 'Лотки в цехе'])
        ->and($photos->pluck('sort_order')->all())->toBe([1, 2])
        ->and($photos->every(fn (GalleryPhoto $photo): bool => $photo->place === 'Новый Уренгой'
            && $photo->category === PhotoCategory::Cables
            && str_starts_with($photo->path, 'gallery/')))->toBeTrue();

    foreach ($photos as $photo) {
        Storage::disk('public')->assertExists($photo->path);
    }
});

it('проверяет раздел и длину объекта', function (): void {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ListGalleryPhotos::class)
        ->callAction('bulkUpload', data: [
            'paths' => [UploadedFile::fake()->image('01 Щит.jpg')],
            'category' => 'kosmos',
            'place' => str_repeat('я', 101),
        ])
        ->assertHasActionErrors(['category', 'place']);

    expect(GalleryPhoto::query()->count())->toBe(0);
});

it('не показывает загрузку пачкой без права добавлять фото', function (): void {
    $this->actingAs(User::factory()->manager()->create());

    Livewire::test(ListGalleryPhotos::class)->assertActionHidden('bulkUpload');
});

it('загружает фото квартир сразу «для дома»', function (): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(ListGalleryPhotos::class)
        ->callAction('bulkUpload', data: [
            'paths' => [UploadedFile::fake()->image('01 Щит в квартире.jpg')],
            'category' => PhotoCategory::Switchboards->value,
            'place' => 'Квартиры, Йошкар-Ола',
            'audience' => Audience::Home->value,
            'is_published' => true,
        ])
        ->assertHasNoActionErrors();

    expect(GalleryPhoto::query()->sole()->audience)->toBe(Audience::Home);
});
