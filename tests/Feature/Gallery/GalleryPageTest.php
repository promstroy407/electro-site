<?php

declare(strict_types=1);

use App\Enums\PhotoCategory;
use App\Models\GalleryPhoto;

it('показывает только опубликованные фото с фильтром по разделам', function (): void {
    GalleryPhoto::factory()->create(['caption' => 'ВРУ с АВР', 'category' => PhotoCategory::Switchboards]);
    GalleryPhoto::factory()->create(['caption' => 'Лотки в цехе', 'category' => PhotoCategory::Cables]);
    GalleryPhoto::factory()->unpublished()->create(['caption' => 'Неудачный кадр']);

    $this->get('/foto')
        ->assertOk()
        ->assertSee('Реальные фото с наших объектов')
        ->assertSee('ВРУ с АВР')
        ->assertSee('Лотки в цехе')
        ->assertDontSee('Неудачный кадр')
        ->assertSee('data-filter="shchity"', escape: false)
        ->assertSee('data-filter="kabel"', escape: false)
        // Раздела без фото в фильтре нет.
        ->assertDontSee('data-filter="pozhar"', escape: false);
});

it('даёт фильтр по объекту, только если с объекта больше одного фото', function (): void {
    GalleryPhoto::factory()->count(2)->create(['place' => 'Торговый центр']);
    GalleryPhoto::factory()->create(['place' => 'Квартира']);

    $this->get('/foto')
        ->assertSee('data-filter="Торговый центр"', escape: false)
        ->assertDontSee('data-filter="Квартира"', escape: false);
});

it('не падает на пустой галерее', function (): void {
    $this->get('/foto')->assertOk()->assertSee('Фото скоро появятся');
});

it('показывает на главной блок фото и карточки объектов из галереи', function (): void {
    GalleryPhoto::factory()->count(2)->create(['place' => 'Цех сортировки отходов', 'category' => PhotoCategory::Lighting]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Реальные фото с наших объектов')
        ->assertSee('Цех сортировки отходов')
        ->assertSee('2 фото');
});

it('показывает видео с обложкой и значком воспроизведения, а на главную его не берёт', function (): void {
    GalleryPhoto::factory()->create(['path' => 'gallery/sdacha.mp4', 'poster_path' => 'gallery/sdacha.jpg', 'caption' => 'Сдача объекта']);

    $this->get('/foto')
        ->assertOk()
        ->assertSee('data-kind="video"', escape: false)
        ->assertSee('/storage/gallery/sdacha.mp4', escape: false)
        ->assertSee('/storage/gallery/sdacha.jpg', escape: false)
        ->assertSee('Смотреть видео: Сдача объекта');

    $this->get('/')->assertOk()->assertDontSee('/storage/gallery/sdacha.jpg', escape: false);
});

it('показывает первый кадр, если у видео нет обложки', function (): void {
    GalleryPhoto::factory()->create(['path' => 'gallery/bez-oblozhki.mp4']);

    $this->get('/foto')->assertOk()->assertSee('<video src=', escape: false);
});
