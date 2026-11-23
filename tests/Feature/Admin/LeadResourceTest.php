<?php

declare(strict_types=1);

use App\Enums\LeadOrigin;
use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Models\Lead;
use App\Models\User;
use Livewire\Livewire;

it('открывает заявки тем, кому положено', function (string $state, int $status): void {
    Lead::factory()->create();

    $this->actingAs(User::factory()->{$state}()->create())
        ->get('/admin/leads')
        ->assertStatus($status);
})->with([
    'администратор' => ['admin', 200],
    'менеджер' => ['manager', 200],
    'редактор' => ['editor', 403],
]);

it('даёт обрабатывать заявку менеджеру, но не редактору', function (string $state, int $status): void {
    $lead = Lead::factory()->create();

    $this->actingAs(User::factory()->{$state}()->create())
        ->get('/admin/leads/'.$lead->id.'/edit')
        ->assertStatus($status);
})->with([
    'менеджер' => ['manager', 200],
    'редактор' => ['editor', 403],
]);

it('разрешает удалять заявки только администратору', function (): void {
    $lead = Lead::factory()->create();

    expect(User::factory()->admin()->create()->can('delete', $lead))->toBeTrue()
        ->and(User::factory()->manager()->create()->can('delete', $lead))->toBeFalse()
        ->and(User::factory()->admin()->create()->can('create', Lead::class))->toBeFalse();
});

it('меняет статус и заметку, но не даёт подменить то, что написал клиент', function (): void {
    $lead = Lead::factory()->create(['name' => 'Иван', 'phone' => '+79170001122']);
    $this->actingAs(User::factory()->manager()->create());

    Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
        ->fillForm([
            'status' => LeadStatus::InProgress->value,
            'manager_note' => 'Перезвонить в пятницу',
            'name' => 'Подмена',
            'phone' => '+70000000000',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $lead->refresh();
    expect($lead->status)->toBe(LeadStatus::InProgress)
        ->and($lead->manager_note)->toBe('Перезвонить в пятницу')
        ->and($lead->name)->toBe('Иван')
        ->and($lead->phone)->toBe('+79170001122');
});

it('валидирует поля обработки заявки', function (array $data, string $field): void {
    $lead = Lead::factory()->create();
    $this->actingAs(User::factory()->manager()->create());

    Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
        ->fillForm($data)
        ->call('save')
        ->assertHasFormErrors([$field]);
})->with([
    'статус не из списка' => [['status' => 'lost'], 'status'],
    'без статуса' => [['status' => null], 'status'],
    'заметка длиннее лимита' => [['manager_note' => str_repeat('я', 2001)], 'manager_note'],
]);

it('показывает в списке заявок, откуда клиент узнал о нас', function (): void {
    Lead::factory()->create(['origin' => LeadOrigin::TwoGis]);

    $this->actingAs(User::factory()->manager()->create())
        ->get('/admin/leads')
        ->assertOk()
        ->assertSee('Откуда узнал')
        ->assertSee('2ГИС');
});

it('показывает организацию заказчика в списке и в карточке заявки', function (): void {
    $lead = Lead::factory()->create(['organization' => 'ООО «Стройка»', 'facility' => 'Склад 2 000 м²']);
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get('/admin/leads')
        ->assertOk()
        ->assertSee('Организация')
        ->assertSee('ООО «Стройка»');

    Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
        ->assertSchemaStateSet(['organization' => 'ООО «Стройка»', 'facility' => 'Склад 2 000 м²']);
});
