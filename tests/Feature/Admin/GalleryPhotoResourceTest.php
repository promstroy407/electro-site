<?php

declare(strict_types=1);

use App\Enums\MediaKind;
use App\Enums\PhotoCategory;
use App\Filament\Resources\GalleryPhotos\Pages\CreateGalleryPhoto;
use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function galleryPhotoData(array $overrides = []): array
{
    return array_merge([
        'path' => UploadedFile::fake()->image('vru.jpg', 1200, 900),
        'caption' => 'ВРУ с автоматическим вводом резерва',
        'category' => PhotoCategory::Switchboards->value,
        'place' => 'Торговый центр',
        'is_published' => true,
        'sort_order' => 0,
    ], $overrides);
}

beforeEach(function (): void {
    Storage::fake('public');
});

it('открывает галерею по правам', function (string $state, int $list, int $create): void {
    $this->actingAs(User::factory()->{$state}()->create());

    $this->get('/admin/gallery')->assertStatus($list);
    $this->get('/admin/gallery/create')->assertStatus($create);
})->with([
    'администратор' => ['admin', 200, 200],
    'редактор' => ['editor', 200, 200],
    'менеджер' => ['manager', 200, 403],
]);

it('не пускает менеджера править фото по прямой ссылке', function (): void {
    $this->actingAs(User::factory()->manager()->create());
    $photo = GalleryPhoto::factory()->create();

    $this->get('/admin/gallery/'.$photo->id.'/edit')->assertForbidden();
});

it('загружает фото в папку галереи', function (): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(CreateGalleryPhoto::class)
        ->fillForm(galleryPhotoData())
        ->call('create')
        ->assertHasNoFormErrors();

    $photo = GalleryPhoto::query()->sole();
    expect($photo->path)->toStartWith('gallery/')
        ->and($photo->category)->toBe(PhotoCategory::Switchboards)
        ->and($photo->place)->toBe('Торговый центр');
    Storage::disk('public')->assertExists($photo->path);
});

it('валидирует каждое поле фото', function (array $overrides, string $field): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(CreateGalleryPhoto::class)
        ->fillForm(galleryPhotoData($overrides))
        ->call('create')
        ->assertHasFormErrors([$field]);
})->with([
    'без файла' => [['path' => null], 'path'],
    'не картинка' => [['path' => UploadedFile::fake()->create('smeta.pdf', 100, 'application/pdf')], 'path'],
    'файл больше 100 МБ' => [['path' => UploadedFile::fake()->image('big.jpg')->size(102401)], 'path'],
    'чужой формат видео' => [['path' => UploadedFile::fake()->create('clip.avi', 1000, 'video/x-msvideo')], 'path'],
    'без подписи' => [['caption' => ''], 'caption'],
    'подпись длиннее колонки' => [['caption' => str_repeat('я', 201)], 'caption'],
    'без раздела' => [['category' => null], 'category'],
    'чужой раздел' => [['category' => 'kosmos'], 'category'],
    'объект длиннее колонки' => [['place' => str_repeat('я', 101)], 'place'],
    'отрицательный порядок' => [['sort_order' => -1], 'sort_order'],
]);

it('загружает видео с обложкой и сам помечает его как видео', function (): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(CreateGalleryPhoto::class)
        ->fillForm(galleryPhotoData([
            'path' => UploadedFile::fake()->create('sdacha.mp4', 5000, 'video/mp4'),
            'poster_path' => UploadedFile::fake()->image('kadr.jpg', 720, 1280),
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    $video = GalleryPhoto::query()->sole();
    expect($video->kind)->toBe(MediaKind::Video)
        ->and($video->poster_path)->toStartWith('gallery/');
    Storage::disk('public')->assertExists([$video->path, (string) $video->poster_path]);
});
