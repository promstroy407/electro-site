<?php

declare(strict_types=1);

use App\Models\PriceItem;
use App\Models\Service;
use App\Support\SiteInfo;

/**
 * Все блоки JSON-LD страницы, разобранные в массивы.
 *
 * @return list<array<string, mixed>>
 */
function jsonLdBlocks(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

    return array_map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
}

/**
 * @param  list<array<string, mixed>>  $blocks
 * @return array<string, mixed>
 */
function jsonLdOfType(array $blocks, string $type): array
{
    foreach ($blocks as $block) {
        if (($block['@type'] ?? null) === $type) {
            return $block;
        }
    }

    throw new RuntimeException("Нет блока {$type}");
}

beforeEach(function (): void {
    config()->set('site.phone', '+7 937 112-48-97');
    config()->set('site.max_channel', 'https://max.ru/join/test-kanal');
    config()->set('site.vk', 'https://vk.ru/test-gruppa');
    app()->forgetInstance(SiteInfo::class);
});

it('описывает организацию на каждой странице', function (string $path): void {
    $organization = jsonLdOfType(jsonLdBlocks($this->get($path)->assertOk()->getContent()), 'Electrician');

    expect($organization['name'])->toBe(config('site.company_name'))
        ->and($organization['telephone'])->toBe('+7 937 112-48-97')
        ->and($organization['@id'])->toBe('http://localhost#organization')
        ->and($organization['sameAs'])->toBe(['https://max.ru/join/test-kanal', 'https://vk.ru/test-gruppa'])
        ->and($organization['address']['addressLocality'])->toBe(config('site.city'));
})->with(['/', '/stati', '/stati/kak-vybrat-kabel', '/uslugi']);

it('размечает частые вопросы на главной', function (): void {
    $faq = jsonLdOfType(jsonLdBlocks($this->get('/')->getContent()), 'FAQPage');

    expect($faq['mainEntity'])->not->toBeEmpty()
        ->and($faq['mainEntity'][0]['@type'])->toBe('Question')
        ->and($faq['mainEntity'][0]['acceptedAnswer']['text'])->not->toBe('');
});

it('размечает статью и путь к ней', function (): void {
    $blocks = jsonLdBlocks($this->get('/stati/kak-vybrat-kabel')->getContent());
    $article = jsonLdOfType($blocks, 'Article');
    $crumbs = jsonLdOfType($blocks, 'BreadcrumbList');

    expect($article['headline'])->toBe('Какой кабель выбрать и почему нельзя экономить на материалах')
        ->and($article['publisher'])->toBe(['@id' => 'http://localhost#organization'])
        ->and(array_column($crumbs['itemListElement'], 'name'))->toBe(['Главная', 'Статьи', $article['headline']]);
});

it('размечает услугу с ценой «от» в рублях', function (): void {
    $direction = Service::factory()->create(['slug' => 'videonablyudenie', 'title' => 'Видеонаблюдение']);
    $child = Service::factory()->childOf($direction)->create(['slug' => 'kamery-dlya-doma', 'title' => 'Камеры для дома']);
    PriceItem::factory()->forService($child)->create(['price_kopecks' => 300_050]);

    $blocks = jsonLdBlocks($this->get('/uslugi/kamery-dlya-doma')->assertOk()->getContent());
    $service = jsonLdOfType($blocks, 'Service');
    $crumbs = jsonLdOfType($blocks, 'BreadcrumbList');

    expect($service['name'])->toBe('Камеры для дома')
        ->and($service['offers']['priceSpecification']['minPrice'])->toBe('3000.50')
        ->and($service['provider'])->toBe(['@id' => 'http://localhost#organization'])
        ->and(array_column($crumbs['itemListElement'], 'name'))->toBe(['Главная', 'Услуги', 'Видеонаблюдение', 'Камеры для дома']);
});

it('не даёт тексту закрыть тег скрипта', function (): void {
    Service::factory()->create(['slug' => 'test', 'title' => 'Взлом </script><script>alert(1)</script>']);

    $html = $this->get('/uslugi/test')->assertOk()->getContent();

    expect($html)->not->toContain('</script><script>alert(1)')
        ->and(jsonLdOfType(jsonLdBlocks($html), 'Service')['name'])->toBe('Взлом </script><script>alert(1)</script>');
});

it('отдаёт llms.txt с услугами и статьями', function (): void {
    Service::factory()->create(['slug' => 'videonablyudenie', 'title' => 'Видеонаблюдение']);

    $this->get('/llms.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('# '.config('site.company_name'), escape: false)
        ->assertSee('[Видеонаблюдение](http://localhost/uslugi/videonablyudenie)', escape: false)
        ->assertSee('(http://localhost/stati/kak-vybrat-kabel)', escape: false);
});
