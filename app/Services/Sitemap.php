<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Routing\UrlGenerator;

/**
 * Список страниц сайта для поисковиков (sitemap.xml): разделы,
 * опубликованные услуги и объекты, статьи, партнёры.
 */
final readonly class Sitemap
{
    public function __construct(
        private ServiceCatalog $services,
        private ProjectCatalog $projects,
        private ArticleCatalog $articles,
        private Calculator $calculator,
        private PartnerCatalog $partners,
        private UrlGenerator $url,
    ) {}

    /**
     * @return list<string> абсолютные адреса страниц
     */
    public function urls(): array
    {
        $urls = [
            $this->url->route('home'),
            $this->url->route('services.index'),
            $this->url->route('subcontract'),
            $this->url->route('for-home'),
            $this->url->route('prices.index'),
            $this->url->route('gallery'),
            $this->url->route('articles.index'),
        ];

        if ($this->calculator->rates()->isNotEmpty()) {
            $urls[] = $this->url->route('calculator');
        }

        foreach ($this->services->directions() as $direction) {
            $urls[] = $this->url->route('services.show', $direction->slug);
            foreach ($direction->children as $child) {
                $urls[] = $this->url->route('services.show', $child->slug);
            }
        }

        foreach ($this->articles->all() as $article) {
            $urls[] = $this->url->route('articles.show', $article->slug);
        }

        $projects = $this->projects->list();
        if ($projects->isNotEmpty()) {
            $urls[] = $this->url->route('projects.index');
            foreach ($projects as $project) {
                $urls[] = $this->url->route('projects.show', $project->slug);
            }
        }

        if ($this->partners->list(limit: 1)->isNotEmpty()) {
            $urls[] = $this->url->route('partners');
        }

        $urls[] = $this->url->route('privacy');

        return array_values(array_unique($urls));
    }
}
