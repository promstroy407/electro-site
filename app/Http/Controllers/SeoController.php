<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ArticleCatalog;
use App\Services\IndexNow;
use App\Services\ServiceCatalog;
use App\Services\Sitemap;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as Views;
use Illuminate\Http\Response;

/**
 * Файлы для поисковиков: robots.txt и sitemap.xml с адресом текущего
 * домена, файл ключа IndexNow, llms.txt для нейросетей.
 */
final readonly class SeoController
{
    public function __construct(
        private Sitemap $sitemap,
        private UrlGenerator $url,
        private Views $views,
        private IndexNow $indexNow,
        private ServiceCatalog $services,
        private ArticleCatalog $articles,
    ) {}

    public function robots(): Response
    {
        $text = "User-agent: *\nDisallow: /admin\nDisallow: /zayavka\n\nSitemap: ".$this->url->route('sitemap')."\n";

        return new Response($text, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $xml = $this->views->make('seo.sitemap', ['urls' => $this->sitemap->urls()])->render();

        return new Response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** Файл ключа IndexNow: Яндекс проверяет по нему, что адреса шлёт владелец сайта. */
    public function indexNowKey(string $key): Response
    {
        abort_if($this->indexNow->key() !== $key, 404);

        return new Response($key, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * llms.txt — краткая «визитка» сайта для нейросетей (llmstxt.org):
     * кто мы, услуги и статьи со ссылками. Собирается из тех же данных,
     * что и страницы сайта.
     */
    public function llms(): Response
    {
        $text = $this->views->make('seo.llms', [
            'directions' => $this->services->directions(),
            'articles' => $this->articles->all(),
        ])->render();

        return new Response($text, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
