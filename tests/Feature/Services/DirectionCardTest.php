<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Models\Service;

/**
 * Направление «для компаний» с 8 опубликованными подуслугами и одной
 * снятой с публикации (её не показываем и не считаем).
 */
function directionWithEightChildren(): Service
{
    $direction = Service::factory()->forAudience(Audience::All)->create([
        'title' => 'Электромонтажные работы',
        'slug' => 'elektromontazh',
        'sort_order' => 1,
    ]);

    foreach (range(1, 8) as $i) {
        Service::factory()->childOf($direction)->forAudience(Audience::Business)->create([
            'title' => 'Подуслуга '.$i,
            'slug' => 'poduslyga-'.$i,
            'sort_order' => $i * 10,
        ]);
    }

    Service::factory()->childOf($direction)->forAudience(Audience::Business)->unpublished()->create([
        'title' => 'Скрытая подуслуга',
        'slug' => 'skrytaya',
        'sort_order' => 5,
    ]);

    return $direction;
}

it('показывает на странице «Услуги» все опубликованные подуслуги направления', function (): void {
    directionWithEightChildren();

    $response = $this->get('/uslugi')->assertOk();

    foreach (range(1, 8) as $i) {
        $response->assertSee('Подуслуга '.$i);
    }
    $response->assertDontSee('Скрытая подуслуга')->assertDontSee('svc__more', escape: false);
});

it('показывает на главной 5 подуслуг и ссылку «Ещё 3 услуги» на направление', function (): void {
    directionWithEightChildren();

    // Только карточка направления: в форме заявки на главной перечислены все услуги.
    $card = str((string) $this->get('/')->assertOk()->getContent())
        ->between('<article class="svc">', '</article>')
        ->toString();

    expect($card)->toContain('Подуслуга 1')
        ->toContain('Подуслуга 5')
        ->not->toContain('Подуслуга 6')
        ->not->toContain('Скрытая подуслуга')
        ->toContain('<a class="svc__more" href="'.route('services.show', 'elektromontazh').'">Ещё 3 услуги →</a>');
});

it('не показывает ссылку «Ещё», если подуслуг не больше пяти', function (): void {
    $direction = Service::factory()->create(['slug' => 'videonablyudenie', 'sort_order' => 1]);
    foreach (range(1, 5) as $i) {
        Service::factory()->childOf($direction)->create(['slug' => 'kamera-'.$i]);
    }

    $this->get('/')->assertOk()->assertDontSee('svc__more', escape: false);
});
