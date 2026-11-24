<?php

declare(strict_types=1);

use App\Support\SiteInfo;

function withRequisites(): void
{
    app()->instance(SiteInfo::class, SiteInfo::fromConfig([
        ...config('site'),
        'legal_name' => 'ИП Иванов Иван Иванович',
        'inn' => '123456789012',
        'ogrnip' => '312345678901234',
    ]));
}

it('показывает реквизиты ИП в подвале', function (): void {
    withRequisites();

    $this->get('/')->assertOk()->assertSee('ИП Иванов Иван Иванович, ИНН 123456789012, ОГРНИП 312345678901234');
});

it('называет ИП оператором в политике', function (): void {
    withRequisites();

    $this->get(route('privacy'))->assertOk()->assertSee('Оператор персональных данных — ИП Иванов Иван Иванович, ИНН 123456789012, ОГРНИП 312345678901234');
});

it('добавляет ИНН в микроразметку организации', function (): void {
    withRequisites();

    $this->get('/')->assertOk()->assertSee('"taxID":"123456789012"', escape: false)->assertSee('"legalName":"ИП Иванов Иван Иванович"', escape: false);
});

it('без реквизитов подвал прежний', function (): void {
    $this->get('/')->assertOk()->assertDontSee('ИНН');
});
