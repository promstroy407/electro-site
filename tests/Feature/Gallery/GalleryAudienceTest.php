<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos;
use App\Models\GalleryPhoto;
use App\Models\User;
use Livewire\Livewire;

it('показывает на главной фото компаний и общие, но не фото квартир', function (): void {
    GalleryPhoto::factory()->forAudience(Audience::Business)->create(['caption' => 'Щитовая цеха']);
    GalleryPhoto::factory()->forAudience(Audience::All)->create(['caption' => 'Видеонаблюдение на объекте']);
    GalleryPhoto::factory()->forAudience(Audience::Home)->create(['caption' => 'Проводка в квартире']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Щитовая цеха')
        ->assertSee('Видеонаблюдение на объекте')
        ->assertDontSee('Проводка в квартире');
});

it('показывает в общей галерее все фото', function (): void {
    GalleryPhoto::factory()->forAudience(Audience::Business)->create(['caption' => 'Щитовая цеха']);
    GalleryPhoto::factory()->forAudience(Audience::Home)->create(['caption' => 'Проводка в квартире']);

    $this->get('/foto')->assertOk()->assertSee('Щитовая цеха')->assertSee('Проводка в квартире');
});

it('меняет «для кого» у отмеченных фото', function (): void {
    $this->actingAs(User::factory()->editor()->create());
    $photos = GalleryPhoto::factory()->count(2)->create();
    $other = GalleryPhoto::factory()->create();

    Livewire::test(ListGalleryPhotos::class)
        ->callTableBulkAction('setAudience', $photos, data: ['audience' => Audience::Home->value])
        ->assertHasNoTableBulkActionErrors();

    expect($photos->map(fn (GalleryPhoto $photo): Audience => $photo->refresh()->audience)->unique()->all())->toBe([Audience::Home])
        ->and($other->refresh()->audience)->toBe(Audience::Business);
});

it('не даёт менеджеру менять «для кого»', function (): void {
    $this->actingAs(User::factory()->manager()->create());
    $photo = GalleryPhoto::factory()->create();

    Livewire::test(ListGalleryPhotos::class)->assertTableBulkActionHidden('setAudience');

    expect($photo->refresh()->audience)->toBe(Audience::Business);
});
