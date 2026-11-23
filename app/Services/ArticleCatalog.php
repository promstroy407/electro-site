<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Article;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Статьи сайта — файлы Markdown в resources/content/stati.
 * Имя файла — адрес статьи: kak-vybrat-kabel.md → /stati/kak-vybrat-kabel.
 */
final readonly class ArticleCatalog
{
    public function __construct(
        private Filesystem $files,
        private MarkdownRenderer $markdown,
    ) {}

    /**
     * @return list<Article> по полю order
     */
    public function all(): array
    {
        $articles = [];
        foreach ($this->files->glob($this->directory().'/*.md') as $path) {
            $articles[] = Article::fromFile(basename($path, '.md'), $this->files->get($path));
        }

        usort($articles, static fn (Article $a, Article $b): int => $a->order <=> $b->order);

        return $articles;
    }

    /**
     * Статьи для частников — все, кроме статей для компаний.
     *
     * @return list<Article>
     */
    public function forHome(int $limit): array
    {
        $articles = array_filter($this->all(), static fn (Article $article): bool => ! $article->isForBusiness());

        return array_slice(array_values($articles), 0, $limit);
    }

    public function find(string $slug): ?Article
    {
        foreach ($this->all() as $article) {
            if ($article->slug === $slug) {
                return $article;
            }
        }

        return null;
    }

    /**
     * Текст статьи в HTML и оглавление по заголовкам второго уровня.
     *
     * @return array{html: HtmlString, toc: list<array{id: string, title: string}>}
     */
    public function render(Article $article): array
    {
        $toc = [];
        $html = (string) $this->markdown->toHtml($article->body);

        // Якоря у заголовков — для оглавления сбоку.
        $html = (string) preg_replace_callback('/<h2>(.*?)<\/h2>/s', function (array $match) use (&$toc): string {
            $title = html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5);
            $id = Str::slug($title);
            $toc[] = ['id' => $id, 'title' => $title];

            return '<h2 id="'.e($id).'">'.$match[1].'</h2>';
        }, $html);

        // Последний абзац с призывом оставить заявку — выделенным блоком.
        $html = (string) preg_replace('/<p>(<strong>[^<]*<\/strong>[^<]*Оставьте заявку[^<]*)<\/p>\s*\z/u', '<p class="cta">$1</p>', trim($html));

        return ['html' => new HtmlString($html), 'toc' => $toc];
    }

    private function directory(): string
    {
        return resource_path('content/stati');
    }
}
