<?php

declare(strict_types=1);

use App\Enums\LeadOrigin;
use App\Enums\LeadStatus;
use App\Jobs\SendLeadNotifications;
use App\Models\Lead;
use App\Models\Service;
use Illuminate\Support\Facades\Bus;

beforeEach(function (): void {
    Bus::fake();
});

it('принимает заявку, нормализует телефон и ставит уведомления в очередь', function (): void {
    $service = Service::factory()->create();

    $this->from('/uslugi/'.$service->slug)
        ->post('/zayavka', leadPayload(['service_id' => $service->id]))
        ->assertRedirect('/uslugi/'.$service->slug.'#zayavka')
        ->assertSessionHas('lead_sent', true);

    $lead = Lead::query()->sole();
    expect($lead->phone)->toBe('+79170001122')
        ->and($lead->service_id)->toBe($service->id)
        ->and($lead->status)->toBe(LeadStatus::New)
        ->and($lead->consent_at)->not->toBeNull()
        ->and($lead->source_url)->toEndWith('/uslugi/'.$service->slug);

    Bus::assertDispatched(SendLeadNotifications::class, fn (SendLeadNotifications $job): bool => $job->leadId === $lead->id);
});

it('сохраняет пустые необязательные поля как null', function (): void {
    $this->post('/zayavka', leadPayload(['email' => '', 'message' => '   ', 'organization' => ' ', 'facility' => '']))->assertRedirect();

    $lead = Lead::query()->sole();
    expect($lead->email)->toBeNull()
        ->and($lead->message)->toBeNull()
        ->and($lead->organization)->toBeNull()
        ->and($lead->facility)->toBeNull()
        ->and($lead->origin)->toBeNull();
});

it('сохраняет, откуда посетитель о нас узнал', function (): void {
    $this->post('/zayavka', leadPayload(['origin' => '2gis']))->assertRedirect();

    expect(Lead::query()->sole()->origin)->toBe(LeadOrigin::TwoGis);
});

it('сохраняет организацию и объект заказчика', function (): void {
    $this->post('/zayavka', leadPayload([
        'organization' => '  ООО «Стройка»  ',
        'facility' => 'Склад 2 000 м², Йошкар-Ола',
    ]))->assertRedirect();

    $lead = Lead::query()->sole();
    expect($lead->organization)->toBe('ООО «Стройка»')
        ->and($lead->facility)->toBe('Склад 2 000 м², Йошкар-Ола');
});

it('показывает в форме все варианты «откуда узнали»', function (): void {
    $response = $this->get('/')->assertOk()->assertSee('Откуда вы о нас узнали?');

    foreach (LeadOrigin::cases() as $origin) {
        $response->assertSee('<option value="'.$origin->value.'"', escape: false);
    }
});

it('валидирует каждое поле формы', function (array $overrides, string $field): void {
    $hidden = Service::factory()->unpublished()->create();
    $overrides = array_map(fn (mixed $value): mixed => $value === 'HIDDEN' ? $hidden->id : $value, $overrides);

    $this->from('/')
        ->post('/zayavka', leadPayload($overrides))
        ->assertRedirect(url('/').'#zayavka')
        ->assertSessionHasErrors($field);

    expect(Lead::query()->count())->toBe(0);
    Bus::assertNothingDispatched();
})->with([
    'без имени' => [['name' => ''], 'name'],
    'имя длиннее колонки' => [['name' => str_repeat('я', 101)], 'name'],
    'без телефона' => [['phone' => ''], 'phone'],
    'буквы в телефоне' => [['phone' => '+7 917 abc 11 22'], 'phone'],
    'слишком короткий телефон' => [['phone' => '12-34-56'], 'phone'],
    'кривой email' => [['email' => 'не-почта'], 'email'],
    'организация длиннее колонки' => [['organization' => str_repeat('я', 151)], 'organization'],
    'объект длиннее колонки' => [['facility' => str_repeat('я', 201)], 'facility'],
    'сообщение длиннее лимита' => [['message' => str_repeat('я', 2001)], 'message'],
    'несуществующая услуга' => [['service_id' => 999999], 'service_id'],
    'неопубликованная услуга' => [['service_id' => 'HIDDEN'], 'service_id'],
    'неизвестный источник' => [['origin' => 'telegram'], 'origin'],
    'без согласия' => [['consent' => null], 'consent'],
    'бот заполнил ловушку' => [['company_website' => 'http://spam.example'], 'company_website'],
]);

it('ограничивает число заявок с одного адреса', function (): void {
    foreach (range(1, 3) as $i) {
        $this->post('/zayavka', leadPayload(['phone' => '+7917000112'.$i]))->assertRedirect();
    }

    $this->post('/zayavka', leadPayload())->assertStatus(429);
    expect(Lead::query()->count())->toBe(3);
});

it('показывает форму на главной и на странице услуги с выбранной услугой', function (): void {
    $direction = Service::factory()->create(['title' => 'Видеонаблюдение']);
    $service = Service::factory()->childOf($direction)->create(['title' => 'Камеры для дома']);

    $this->get('/')->assertOk()->assertSee('id="zayavka"', escape: false);

    $this->get('/uslugi/'.$service->slug)
        ->assertOk()
        ->assertSee('<option value="'.$service->id.'" selected', escape: false);
});

it('показывает благодарность после отправки', function (): void {
    $this->withSession(['lead_sent' => true])
        ->get('/')
        ->assertSee('Спасибо! Заявка отправлена.');
});

it('открывает страницу политики конфиденциальности', function (): void {
    $this->get('/politika-konfidencialnosti')->assertOk()->assertSee('152-ФЗ');
});

it('база принимает каждый вариант источника из enum', function (LeadOrigin $origin): void {
    $lead = Lead::factory()->create(['origin' => $origin]);

    expect($lead->refresh()->origin)->toBe($origin);
})->with(LeadOrigin::cases());
