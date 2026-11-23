<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\Service;

it('показывает раздел «Наши объекты» только с опубликованными объектами', function (): void {
    Project::factory()->create(['title' => 'Склад на Строителей']);
    Project::factory()->unpublished()->create(['title' => 'Черновик объекта']);

    $this->get('/obekty')->assertOk()->assertSee('Склад на Строителей')->assertDontSee('Черновик объекта');
});

it('фильтрует объекты по направлению, учитывая его услуги', function (): void {
    $fire = Service::factory()->create(['title' => 'Пожарная сигнализация']);
    $aps = Service::factory()->childOf($fire)->create();
    $video = Service::factory()->create(['title' => 'Видеонаблюдение']);

    Project::factory()->create(['title' => 'Объект с АПС'])->services()->attach($aps);
    Project::factory()->create(['title' => 'Объект с камерами'])->services()->attach($video);

    $this->get('/obekty?rabota='.$fire->slug)
        ->assertOk()
        ->assertSee('Объект с АПС')
        ->assertDontSee('Объект с камерами');

    $this->get('/obekty?rabota=net-takogo')
        ->assertOk()
        ->assertSee('Объект с АПС')
        ->assertSee('Объект с камерами');
});

it('показывает на карточке только опубликованные метки услуг', function (): void {
    $shown = Service::factory()->create(['title' => 'Монтаж СКУД']);
    $hidden = Service::factory()->unpublished()->create(['title' => 'Скрытая работа']);
    Project::factory()->create()->services()->attach([$shown->id, $hidden->id]);

    $this->get('/obekty')->assertSee('Монтаж СКУД')->assertDontSee('Скрытая работа');
});

it('открывает страницу объекта с фото, описанием и метками', function (): void {
    $service = Service::factory()->create(['title' => 'Видеонаблюдение']);
    $project = Project::factory()->withPhotos(['portfolio/a.jpg', 'portfolio/b.jpg'])->create([
        'title' => 'Частный дом',
        'city' => 'Медведево',
        'year' => 2024,
        'body' => "## Что сделали\n\n<script>alert(1)</script>",
    ]);
    $project->services()->attach($service);

    $this->get('/obekty/'.$project->slug)
        ->assertOk()
        ->assertSee('Частный дом')
        ->assertSee('Медведево, 2024')
        ->assertSee('/storage/portfolio/a.jpg', escape: false)
        ->assertSee('/storage/portfolio/b.jpg', escape: false)
        ->assertSee('<h2>Что сделали</h2>', escape: false)
        ->assertDontSee('<script>alert(1)</script>', escape: false)
        ->assertSee('Видеонаблюдение');
});

it('отдаёт 404 на неопубликованный и несуществующий объект', function (): void {
    $hidden = Project::factory()->unpublished()->create();

    $this->get('/obekty/'.$hidden->slug)->assertNotFound();
    $this->get('/obekty/net-takogo')->assertNotFound();
});

it('показывает объекты на странице услуги и на главной', function (): void {
    $direction = Service::factory()->create();
    $child = Service::factory()->childOf($direction)->create();
    Project::factory()->create(['title' => 'Объект направления'])->services()->attach($child);
    Project::factory()->create(['title' => 'Чужой объект']);

    $this->get('/uslugi/'.$direction->slug)
        ->assertOk()
        ->assertSee('Объекты с этой работой')
        ->assertSee('Объект направления')
        ->assertDontSee('Чужой объект');

    $this->get('/')->assertOk()->assertSee('Наши объекты')->assertSee('Объект направления');
});

it('не показывает блок объектов, когда их нет', function (): void {
    $service = Service::factory()->create();

    $this->get('/uslugi/'.$service->slug)->assertOk()->assertDontSee('Объекты с этой работой');
    $this->get('/obekty')->assertOk()->assertSee('Объекты скоро появятся');
});
