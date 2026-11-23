<?php

declare(strict_types=1);

use App\Services\ArticleCatalog;

it('показывает список всех статей', function (): void {
    $articles = app(ArticleCatalog::class)->all();

    expect($articles)->toHaveCount(34);

    $response = $this->get('/stati')->assertOk();
    foreach ($articles as $article) {
        $response->assertSee($article->title)->assertSee('/stati/'.$article->slug, escape: false);
    }
});

it('открывает каждую статью с оглавлением, иллюстрацией и заявкой', function (): void {
    foreach (app(ArticleCatalog::class)->all() as $article) {
        $this->get('/stati/'.$article->slug)
            ->assertOk()
            ->assertSee('<title>'.e($article->seoTitle), escape: false)
            ->assertSee('<h2 id="', escape: false)
            ->assertSee('<svg', escape: false)
            ->assertSee('Нужен расчёт?');
    }
});

it('выделяет призыв оставить заявку в конце статьи', function (): void {
    $this->get('/stati/kak-vybrat-kabel')->assertSee('<p class="cta">', escape: false);
});

it('отвечает 404 на неизвестную статью', function (): void {
    $this->get('/stati/net-takoy-stati')->assertNotFound();
    $this->get('/stati/Плохой_Адрес')->assertNotFound();
});

it('показывает три статьи на главной', function (): void {
    $first = app(ArticleCatalog::class)->all()[0];

    $this->get('/')->assertOk()->assertSee($first->title);
});
