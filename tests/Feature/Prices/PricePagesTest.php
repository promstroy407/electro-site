<?php

declare(strict_types=1);

use App\Enums\PriceUnit;
use App\Models\PriceItem;
use App\Models\Service;

$nbsp = "\u{00A0}";

it('показывает «от» минимальной цены на карточке направления, учитывая его услуги', function () use ($nbsp): void {
    $direction = Service::factory()->create(['title' => 'Видеонаблюдение']);
    $child = Service::factory()->childOf($direction)->create();
    PriceItem::factory()->forService($direction)->create(['price_kopecks' => 300_000]);
    PriceItem::factory()->forService($child)->create(['price_kopecks' => 250_000]);
    PriceItem::factory()->forService($child)->unpublished()->create(['price_kopecks' => 100]);

    $this->get('/uslugi')->assertOk()->assertSee('от 2'.$nbsp.'500'.$nbsp.'₽');
});

it('не учитывает цены неопубликованной услуги', function () use ($nbsp): void {
    $direction = Service::factory()->create();
    $hidden = Service::factory()->childOf($direction)->unpublished()->create();
    PriceItem::factory()->forService($direction)->create(['price_kopecks' => 500_000]);
    PriceItem::factory()->forService($hidden)->create(['price_kopecks' => 100_000]);

    $this->get('/uslugi')->assertSee('от 5'.$nbsp.'000'.$nbsp.'₽')->assertDontSee('1'.$nbsp.'000'.$nbsp.'₽');
});

it('показывает таблицу цен и «от» на странице услуги', function () use ($nbsp): void {
    $service = Service::factory()->create();
    PriceItem::factory()->forService($service)->create([
        'title' => 'Монтаж IP-камеры',
        'price_kopecks' => 250_050,
        'unit' => PriceUnit::Piece,
        'is_from' => true,
    ]);
    PriceItem::factory()->forService($service)->create([
        'title' => 'Выезд мастера',
        'price_kopecks' => 300_000,
        'unit' => PriceUnit::Service,
        'is_from' => false,
    ]);
    PriceItem::factory()->forService($service)->unpublished()->create(['title' => 'Скрытая позиция']);

    $this->get('/uslugi/'.$service->slug)
        ->assertOk()
        ->assertSee('Монтаж IP-камеры')
        ->assertSee('от 2'.$nbsp.'500,50'.$nbsp.'₽')
        ->assertSee('/ шт.')
        ->assertSee('Выезд мастера')
        ->assertSee('3'.$nbsp.'000'.$nbsp.'₽')
        ->assertDontSee('Скрытая позиция');
});

it('показывает на странице направления цены его услуг', function (): void {
    $direction = Service::factory()->create();
    $child = Service::factory()->childOf($direction)->create(['title' => 'Камеры для дома']);
    $hidden = Service::factory()->childOf($direction)->unpublished()->create();
    PriceItem::factory()->forService($child)->create(['title' => 'Комплект камер']);
    PriceItem::factory()->forService($hidden)->create(['title' => 'Цена скрытой услуги']);

    $this->get('/uslugi/'.$direction->slug)
        ->assertOk()
        ->assertSee('Комплект камер')
        ->assertDontSee('Цена скрытой услуги');
});

it('собирает страницу «Цены» по направлениям и пропускает пустые', function (): void {
    $withPrices = Service::factory()->create(['title' => 'Пожарная сигнализация']);
    Service::factory()->create(['title' => 'Направление без цен']);
    $hiddenDirection = Service::factory()->unpublished()->create(['title' => 'Скрытое направление']);
    PriceItem::factory()->forService($withPrices)->create(['title' => 'Монтаж извещателя']);
    PriceItem::factory()->forService($hiddenDirection)->create(['title' => 'Позиция скрытого направления']);

    $this->get('/ceny')
        ->assertOk()
        ->assertSee('Монтаж извещателя')
        ->assertSee('Пожарная сигнализация')
        ->assertDontSee('Позиция скрытого направления')
        ->assertSee('id="zayavka"', escape: false);
});

it('страница «Цены» не падает без прайса', function (): void {
    $this->get('/ceny')->assertOk()->assertSee('Прайс скоро появится');
});

it('показывает услуги без прайса с ценой «по запросу»', function (): void {
    $direction = Service::factory()->create(['title' => 'Видеонаблюдение']);
    Service::factory()->childOf($direction)->create(['title' => 'Камеры для дома']);

    $this->get('/ceny')->assertOk()->assertSee('Камеры для дома')->assertSee('по запросу');
    $this->get('/uslugi')->assertSee('Цена по запросу');
});

it('показывает «от» по цене за штуку, а не за метр кабеля', function () use ($nbsp): void {
    $direction = Service::factory()->create(['title' => 'Пожарная сигнализация']);
    $child = Service::factory()->childOf($direction)->create();
    PriceItem::factory()->forService($child)->create(['price_kopecks' => 3_000, 'unit' => PriceUnit::Meter]);
    PriceItem::factory()->forService($child)->create(['price_kopecks' => 30_000, 'unit' => PriceUnit::Piece]);

    $this->get('/uslugi')->assertSee('от 300'.$nbsp.'₽')->assertDontSee('от 30'.$nbsp.'₽');
    $this->get('/uslugi/'.$child->slug)->assertSee('от 300'.$nbsp.'₽');
});

it('берёт цену за метр, если других цен у услуги нет', function () use ($nbsp): void {
    $direction = Service::factory()->create();
    PriceItem::factory()->forService($direction)->create(['price_kopecks' => 6_000, 'unit' => PriceUnit::Meter]);

    $this->get('/uslugi')->assertSee('от 60'.$nbsp.'₽');
});

it('на главной для компаний цен частникам нет', function () use ($nbsp): void {
    $direction = Service::factory()->create(['title' => 'Видеонаблюдение']);
    PriceItem::factory()->forService($direction)->create(['price_kopecks' => 250_000]);

    $this->get('/')->assertOk()->assertSee('Видеонаблюдение')->assertDontSee('от 2'.$nbsp.'500'.$nbsp.'₽');
});
