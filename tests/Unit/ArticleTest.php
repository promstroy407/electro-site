<?php

declare(strict_types=1);

use App\Support\Article;

function articleFile(string $meta, string $body = "Текст статьи.\n"): string
{
    return "---\n".$meta."\n---\n\n".$body;
}

$valid = "title: Заголовок\ntag: Тег\nillustration: kabel\nseo_title: SEO\nseo_description: Описание\nkeywords: слова\nexcerpt: Анонс\norder: 2";

it('читает шапку и текст статьи', function () use ($valid): void {
    $article = Article::fromFile('test', articleFile($valid, "## Раздел\n\nТекст: с двоеточием.\n"));

    expect($article->title)->toBe('Заголовок')
        ->and($article->illustration)->toBe('kabel')
        ->and($article->order)->toBe(2)
        ->and($article->body)->toBe("## Раздел\n\nТекст: с двоеточием.")
        ->and($article->readingMinutes())->toBe(1);
});

it('отказывается читать статью без шапки, без поля или с чужой иллюстрацией', function (string $contents): void {
    Article::fromFile('test', $contents);
})->with([
    'без шапки' => ['Просто текст'],
    'без заголовка' => [articleFile("tag: Тег\nillustration: kabel\nseo_title: S\nseo_description: D\nexcerpt: A\norder: 1")],
    'чужая иллюстрация' => [articleFile("title: З\ntag: Т\nillustration: kosmos\nseo_title: S\nseo_description: D\nexcerpt: A\norder: 1")],
])->throws(InvalidArgumentException::class);

it('отличает статьи для компаний по тегу', function () use ($valid): void {
    $forHome = Article::fromFile('dom', articleFile($valid));
    $forBusiness = Article::fromFile('biznes', articleFile(str_replace('tag: Тег', 'tag: '.Article::BUSINESS_TAG, $valid)));

    expect($forHome->isForBusiness())->toBeFalse()
        ->and($forBusiness->isForBusiness())->toBeTrue();
});
