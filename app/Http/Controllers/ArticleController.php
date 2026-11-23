<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ArticleCatalog;
use App\Support\Article;
use Illuminate\Contracts\View\View;

final readonly class ArticleController
{
    public function __construct(
        private ArticleCatalog $articles,
    ) {}

    public function index(): View
    {
        return view('articles.index', [
            'articles' => $this->articles->all(),
        ]);
    }

    public function show(string $slug): View
    {
        $article = $this->articles->find($slug);

        abort_if($article === null, 404);

        $rendered = $this->articles->render($article);

        return view('articles.show', [
            'article' => $article,
            'html' => $rendered['html'],
            'toc' => $rendered['toc'],
            'others' => array_values(array_filter(
                $this->articles->all(),
                static fn (Article $other): bool => $other->slug !== $article->slug,
            )),
        ]);
    }
}
