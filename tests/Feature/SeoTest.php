<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\Service;

it('отдаёт robots.txt со ссылкой на карту сайта и закрытой админкой', function (): void {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: http://localhost/sitemap.xml');
});

it('перечисляет в карте сайта разделы, опубликованные услуги, статьи и объекты', function (): void {
    $direction = Service::factory()->create(['slug' => 'videonablyudenie']);
    Service::factory()->childOf($direction)->create(['slug' => 'kamery-dlya-doma']);
    Service::factory()->unpublished()->create(['slug' => 'chernovik']);
    Project::factory()->create(['slug' => 'sklad']);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<loc>http://localhost</loc>', escape: false)
        ->assertSee('<loc>http://localhost/foto</loc>', escape: false)
        ->assertSee('/uslugi/videonablyudenie', escape: false)
        ->assertSee('/uslugi/kamery-dlya-doma', escape: false)
        ->assertSee('/stati/kak-vybrat-kabel', escape: false)
        ->assertSee('/obekty/sklad', escape: false)
        ->assertDontSee('chernovik', escape: false);
});

it('кладёт файл подтверждения прав для Яндекс Вебмастера', function (): void {
    expect(file_get_contents(public_path('yandex_55b781fe79bf44fc.html')))->toContain('Verification: 55b781fe79bf44fc');
});
