<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Models\GalleryPhoto;
use App\Models\Service;
use App\Services\ArticleCatalog;
use App\Support\SiteInfo;

it('показывает раздел для дома с преимуществами, этапами, вопросами и формой', function (): void {
    $this->get('/dlya-doma')
        ->assertOk()
        ->assertSee('<h1 class="h2">Электрик для квартиры и частного дома</h1>', escape: false)
        ->assertSee('<title>Электрик в '.app(SiteInfo::class)->cityIn.' — электромонтаж в квартире и доме', escape: false)
        ->assertSee('Своя бригада, без посредников')
        ->assertSee('Штробим с пылесосом')
        ->assertSee('Как проходит работа')
        ->assertSee('Будет ли грязь?')
        ->assertSee('href="'.route('calculator').'"', escape: false)
        ->assertSee('id="zayavka"', escape: false)
        ->assertSee('"@type":"FAQPage"', escape: false)
        ->assertSee('"@type":"BreadcrumbList"', escape: false);
});

it('не обещает частникам пожарную сигнализацию: лицензии МЧС нет', function (): void {
    $page = (string) $this->get('/dlya-doma')->assertOk()->getContent();

    $works = str($page)->between('id="raboty"', 'id="etapy"')->toString();
    expect($works)->not->toContain('ожарн');
});

it('предлагает в форме услуги для дома и общие, но не только для бизнеса', function (): void {
    Service::factory()->forAudience(Audience::Home)->create(['title' => 'Проводка в квартире']);
    Service::factory()->forAudience(Audience::Business)->create(['title' => 'Электрика цеха']);

    $this->get('/dlya-doma')
        ->assertOk()
        ->assertSee('Проводка в квартире')
        ->assertDontSee('Электрика цеха');
});

it('показывает в разделе для дома только статьи не для компаний', function (): void {
    $articles = app(ArticleCatalog::class)->forHome(limit: 3);

    expect($articles)->toHaveCount(3);
    foreach ($articles as $article) {
        expect($article->isForBusiness())->toBeFalse();
    }

    $this->get('/dlya-doma')->assertOk()->assertSee(route('articles.show', $articles[0]->slug), escape: false);
});

it('ведёт в раздел для дома из меню и добавляет его в sitemap', function (): void {
    $this->get('/')->assertOk()->assertSee('href="'.route('for-home').'"', escape: false);

    $page = (string) $this->get('/dlya-doma')->getContent();
    expect($page)->toMatch('#href="'.preg_quote(route('for-home'), '#').'"\s+aria-current="page"#');

    $this->get('/sitemap.xml')->assertOk()->assertSee(route('for-home'), escape: false);
});

it('показывает примеры работ — фото для дома, но не фото компаний', function (): void {
    GalleryPhoto::factory()->forAudience(Audience::Home)->create(['caption' => 'Щит в квартире']);
    GalleryPhoto::factory()->forAudience(Audience::Business)->create(['caption' => 'Кабельная эстакада цеха']);

    $page = (string) $this->get('/dlya-doma')->assertOk()->getContent();

    $examples = str($page)->between('id="primery"', 'id="raboty"')->toString();
    expect($examples)->toContain('Щит в квартире')
        ->and($examples)->not->toContain('Кабельная эстакада цеха');
});

it('не показывает блок примеров, пока нет фото для дома', function (): void {
    GalleryPhoto::factory()->forAudience(Audience::Business)->create();

    $this->get('/dlya-doma')->assertOk()->assertDontSee('id="primery"', escape: false);
});
