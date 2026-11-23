<?php

declare(strict_types=1);

use App\Support\SiteInfo;

function withMetrika(string $id): void
{
    config()->set('site.metrika_id', $id);
    app()->forgetInstance(SiteInfo::class);
}

it('ставит счётчик Метрики на страницы сайта, если задан номер', function (): void {
    withMetrika('114072347');

    $this->get('/')
        ->assertOk()
        ->assertSee("ym(114072347, 'init'", escape: false)
        ->assertDontSee('reachGoal', escape: false);
    $this->get('/politika-konfidencialnosti')->assertOk()->assertSee('Яндекс Метрика');
});

it('отмечает в Метрике цель после отправки заявки', function (): void {
    withMetrika('114072347');

    $this->withSession(['lead_sent' => true])
        ->get('/')
        ->assertSee("ym(114072347, 'reachGoal', 'lead')", escape: false);
});

it('не ставит счётчик без номера', function (): void {
    withMetrika('');

    $this->get('/')->assertOk()->assertDontSee('mc.yandex.ru', escape: false);
    $this->get('/politika-konfidencialnosti')->assertOk()->assertDontSee('Яндекс Метрика');
});
