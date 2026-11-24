<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;
use App\Support\Article;
use App\Support\SiteInfo;
use Illuminate\Contracts\Routing\UrlGenerator;

/**
 * Микроразметка schema.org (JSON-LD) для Яндекса и других поисковиков:
 * кто мы (организация), что за страница (услуга, статья), путь к ней и
 * частые вопросы. Данные — те же, что видит посетитель, ничего сверх.
 */
final readonly class StructuredData
{
    public function __construct(
        private SiteInfo $site,
        private UrlGenerator $url,
        private RubleFormatter $rubles,
    ) {}

    /**
     * Организация: на каждой странице, остальные блоки ссылаются на неё по @id.
     *
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $home = $this->url->route('home');

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Electrician',
            '@id' => $this->organizationId(),
            'name' => $this->site->companyName,
            'legalName' => $this->site->legalName,
            'taxID' => $this->site->inn,
            'description' => 'Электромонтаж и слаботочные системы под ключ: электрика до 10 кВ, пожарная и охранная сигнализация, видеонаблюдение, СКУД, СКС.',
            'url' => $home,
            'logo' => $this->url->asset('img/logo-mark.png'),
            'image' => $this->url->asset('img/og.jpg'),
            'telephone' => $this->site->phone,
            'email' => $this->site->email,
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'addressLocality' => $this->site->city,
                'addressRegion' => $this->site->region,
                'addressCountry' => 'RU',
            ]),
            'areaServed' => array_values(array_filter([
                $this->site->city !== '' ? ['@type' => 'City', 'name' => $this->site->city] : null,
                ['@type' => 'Country', 'name' => 'Россия'],
            ])),
            'sameAs' => array_values(array_filter([$this->site->telegram, ...array_column($this->site->socialLinks(), 'url')])),
        ], fn (mixed $value): bool => $value !== '' && $value !== []);
    }

    /**
     * @param  list<array{string, string}>  $crumbs  [название, адрес], от главной к текущей
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $crumbs): array
    {
        $items = [];
        foreach (array_values($crumbs) as $i => [$name, $url]) {
            $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => $url];
        }

        return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }

    /**
     * @return array<string, mixed>
     */
    public function article(Article $article): array
    {
        $url = $this->url->route('articles.show', $article->slug);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->seoDescription,
            'inLanguage' => 'ru',
            'url' => $url,
            'mainEntityOfPage' => $url,
            'image' => $this->url->asset('img/og.jpg'),
            'author' => ['@id' => $this->organizationId()],
            'publisher' => ['@id' => $this->organizationId()],
        ];
    }

    /**
     * Услуга; цена «от» — та же, что на странице, в рублях с точкой.
     *
     * @return array<string, mixed>
     */
    public function service(Service $service, ?int $priceFromKopecks): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->title,
            'url' => $this->url->route('services.show', $service->slug),
            'provider' => ['@id' => $this->organizationId()],
            'areaServed' => ['@type' => 'Country', 'name' => 'Россия'],
        ];

        if ($service->summary !== null && $service->summary !== '') {
            $data['description'] = $service->summary;
        }

        if ($priceFromKopecks !== null) {
            $data['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => 'RUB',
                'priceSpecification' => [
                    '@type' => 'PriceSpecification',
                    'priceCurrency' => 'RUB',
                    'minPrice' => $this->rubles->toDecimal($priceFromKopecks),
                ],
            ];
        }

        return $data;
    }

    /**
     * @param  list<array{string, string}>  $questions  [вопрос, ответ]
     * @return array<string, mixed>
     */
    public function faq(array $questions): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $qa): array => [
                '@type' => 'Question',
                'name' => $qa[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
            ], $questions),
        ];
    }

    private function organizationId(): string
    {
        return $this->url->route('home').'#organization';
    }
}
