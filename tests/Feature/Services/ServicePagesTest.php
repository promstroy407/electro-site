<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Enums\ServiceIcon;
use App\Models\Service;

it('показывает на главной и в каталоге только опубликованные направления', function (): void {
    Service::factory()->create(['title' => 'Видеонаблюдение']);
    Service::factory()->unpublished()->create(['title' => 'Черновик направления']);

    $this->get('/')->assertOk()->assertSee('Видеонаблюдение')->assertDontSee('Черновик направления');
    $this->get('/uslugi')->assertOk()->assertSee('Видеонаблюдение')->assertDontSee('Черновик направления');
});

it('показывает в карточке направления только опубликованные услуги', function (): void {
    $direction = Service::factory()->create(['title' => 'СКУД']);
    Service::factory()->childOf($direction)->create(['title' => 'Турникеты']);
    Service::factory()->childOf($direction)->unpublished()->create(['title' => 'Скрытая услуга']);

    $this->get('/uslugi')->assertSee('Турникеты')->assertDontSee('Скрытая услуга');
    $this->get('/uslugi/'.$direction->slug)->assertSee('Турникеты')->assertDontSee('Скрытая услуга');
});

it('фильтрует каталог по аудитории, услуги «всем» видны везде', function (): void {
    Service::factory()->forAudience(Audience::Business)->create(['title' => 'Пожарная сигнализация']);
    Service::factory()->forAudience(Audience::Home)->create(['title' => 'Электрика в квартире']);
    Service::factory()->forAudience(Audience::All)->create(['title' => 'Видеонаблюдение']);

    $this->get('/uslugi?dlya=business')
        ->assertSee('Пожарная сигнализация')->assertSee('Видеонаблюдение')->assertDontSee('Электрика в квартире');

    $this->get('/uslugi?dlya=home')
        ->assertSee('Электрика в квартире')->assertSee('Видеонаблюдение')->assertDontSee('Пожарная сигнализация');

    $this->get('/uslugi?dlya=чепуха')
        ->assertOk()->assertSee('Пожарная сигнализация')->assertSee('Электрика в квартире');
});

it('открывает опубликованную услугу с описанием и крошками', function (): void {
    $direction = Service::factory()->create(['title' => 'Электромонтаж']);
    $service = Service::factory()->childOf($direction)->create([
        'title' => 'Сборка щитов',
        'body' => "## Что входит\n\n- сборка по схеме",
        'meta_title' => 'Сборка электрощитов в Йошкар-Оле',
    ]);

    $this->get('/uslugi/'.$service->slug)
        ->assertOk()
        ->assertSee('Сборка щитов')
        ->assertSee('<h2>Что входит</h2>', escape: false)
        ->assertSee('<title>Сборка электрощитов в Йошкар-Оле', escape: false)
        ->assertSee('Электромонтаж');
});

it('экранирует HTML и опасные ссылки в описании', function (): void {
    $service = Service::factory()->create([
        'body' => "<script>alert(1)</script>\n\n[ссылка](javascript:alert(2))",
    ]);

    $this->get('/uslugi/'.$service->slug)
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', escape: false)
        ->assertDontSee('javascript:alert(2)', escape: false);
});

it('отдаёт 404 на неопубликованную, на услугу в скрытом направлении и на несуществующую', function (): void {
    $hidden = Service::factory()->unpublished()->create();
    $hiddenDirection = Service::factory()->unpublished()->create();
    $childOfHidden = Service::factory()->childOf($hiddenDirection)->create();

    $this->get('/uslugi/'.$hidden->slug)->assertNotFound();
    $this->get('/uslugi/'.$childOfHidden->slug)->assertNotFound();
    $this->get('/uslugi/net-takoy-uslugi')->assertNotFound();
    $this->get('/uslugi/Плохой_Адрес')->assertNotFound();
});

it('рисует на главной фото первого экрана и иконки направлений', function (): void {
    Service::factory()->create(['title' => 'Пожарная сигнализация', 'icon' => ServiceIcon::Fire]);
    Service::factory()->create(['title' => 'Без иконки', 'icon' => null]);

    $this->get('/')
        ->assertOk()
        ->assertSee('/img/hero.jpg', escape: false)
        // Пламя — у пожарной сигнализации, молния — у направления без иконки.
        ->assertSee('<path d="M12 22c4 0 7-3 7-7', escape: false)
        ->assertSee('<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>', escape: false);
});
