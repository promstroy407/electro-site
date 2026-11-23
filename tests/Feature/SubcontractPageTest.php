<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Models\Service;

it('показывает страницу субподряда с работами, этапами, вопросами и формой', function (): void {
    $this->get('/subpodryad')
        ->assertOk()
        ->assertSee('<h1 class="h2">Субподряд на электромонтаж и слаботочные системы</h1>', escape: false)
        ->assertSee('Что берём на субподряд')
        ->assertSee('Как работаем с генподрядчиком')
        ->assertSee('Работаете с материалом генподрядчика?')
        ->assertSee('id="zayavka"', escape: false)
        ->assertSee('"@type":"FAQPage"', escape: false)
        ->assertSee('"@type":"BreadcrumbList"', escape: false);
});

it('не обещает на странице субподряда пожарную сигнализацию: лицензии МЧС нет', function (): void {
    $response = $this->get('/subpodryad')->assertOk();

    $works = str($response->getContent())->between('id="raboty"', 'id="etapy"')->toString();
    expect($works)->not->toContain('ожарн');
});

it('предлагает в форме услуги для бизнеса и общие, но не только для дома', function (): void {
    Service::factory()->forAudience(Audience::Business)->create(['title' => 'Электрика цеха']);
    Service::factory()->forAudience(Audience::Home)->create(['title' => 'Проводка в квартире']);

    $this->get('/subpodryad')
        ->assertOk()
        ->assertSee('Электрика цеха')
        ->assertDontSee('Проводка в квартире');
});

it('ведёт на страницу субподряда из меню и с главной и добавляет её в sitemap', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.route('subcontract').'"', escape: false)
        ->assertSee('Подробнее о субподряде');

    $page = (string) $this->get('/subpodryad')->getContent();
    expect($page)->toMatch('#href="'.preg_quote(route('subcontract'), '#').'"\s+aria-current="page"#');

    $this->get('/sitemap.xml')->assertOk()->assertSee(route('subcontract'), escape: false);
});
