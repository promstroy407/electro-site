<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Models\Service;
use App\Services\SeasonalOffer;

function concreteHeating(): Service
{
    return Service::factory()->forAudience(Audience::Business)->create([
        'title' => 'Прогрев бетона зимой',
        'slug' => SeasonalOffer::WINTER_SERVICE_SLUG,
        'summary' => 'Прогрев проводом ПНСВ под ключ. От 1 500 ₽ за 1 м³.',
    ]);
}

it('зимой показывает на главной блок прогрева бетона со ссылкой на услугу', function (string $date): void {
    concreteHeating();
    $this->travelTo($date);

    $this->get('/')
        ->assertOk()
        ->assertSee('Сейчас сезон')
        ->assertSee('Прогрев проводом ПНСВ под ключ. От 1 500 ₽ за 1 м³.')
        ->assertSee(route('services.show', SeasonalOffer::WINTER_SERVICE_SLUG), escape: false);
})->with(['2026-10-01', '2026-11-18', '2027-01-15', '2027-04-30']);

it('летом блока нет', function (string $date): void {
    concreteHeating();
    $this->travelTo($date);

    $this->get('/')->assertOk()->assertDontSee('Сейчас сезон');
})->with(['2027-05-01', '2027-07-15', '2027-09-30']);

it('блока нет, если услугу сняли с публикации', function (): void {
    Service::factory()->unpublished()->create(['slug' => SeasonalOffer::WINTER_SERVICE_SLUG]);
    $this->travelTo('2026-11-18');

    $this->get('/')->assertOk()->assertDontSee('Сейчас сезон');
});

it('блока нет, если услуги нет', function (): void {
    $this->travelTo('2026-11-18');

    $this->get('/')->assertOk()->assertDontSee('Сейчас сезон');
});
