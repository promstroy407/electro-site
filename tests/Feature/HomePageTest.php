<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Models\Service;
use App\Support\SiteInfo;

it('открывает главную и показывает название компании из настроек', function (): void {
    config()->set('site.company_name', 'ООО «Проверка»');
    app()->forgetInstance(SiteInfo::class);

    $this->get('/')
        ->assertOk()
        ->assertSee('ООО «Проверка»');
});

it('отдаёт мессенджерам заголовок, описание и картинку для превью ссылки', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('<meta property="og:title"', escape: false)
        ->assertSee('<meta property="og:description"', escape: false)
        ->assertSee('/img/og.jpg', escape: false)
        ->assertSee('/favicon.ico', escape: false)
        ->assertSee('/img/logo-mark.png', escape: false);
});

// «Для дома» в меню есть с 18.11: частникам — свой раздел /dlya-doma.
it('главная — для компаний: для кого работаем, без цен и калькулятора в меню', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('Для кого работаем')
        ->assertSee('Генподрядчикам и застройщикам')
        ->assertSee('Отправить проект на расчёт')
        ->assertDontSee('>Цены</a>', escape: false)
        ->assertDontSee(route('calculator').'"', escape: false);
});

it('показывает на главной услуги для бизнеса и общие, но не только для дома', function (): void {
    Service::factory()->forAudience(Audience::Business)->create(['title' => 'Электрика цеха']);
    Service::factory()->forAudience(Audience::All)->create(['title' => 'Видеонаблюдение']);
    Service::factory()->forAudience(Audience::Home)->create(['title' => 'Проводка в квартире']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Электрика цеха')
        ->assertSee('Видеонаблюдение')
        ->assertDontSee('Проводка в квартире');
});

it('страницы для частников остаются доступны по прямым ссылкам', function (string $url): void {
    $this->get($url)->assertOk();
})->with(['/ceny', '/uslugi?dlya=home']);
