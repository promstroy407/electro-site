<?php

declare(strict_types=1);

use App\Enums\PriceUnit;
use App\Filament\Resources\PriceItems\Pages\CreatePriceItem;
use App\Filament\Resources\PriceItems\Pages\EditPriceItem;
use App\Models\PriceItem;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function priceData(int $serviceId, array $overrides = []): array
{
    return array_merge([
        'service_id' => $serviceId,
        'title' => 'Монтаж IP-камеры',
        'price_kopecks' => '2 500,50',
        'unit' => PriceUnit::Piece->value,
        'is_from' => true,
        'is_published' => true,
        'sort_order' => 10,
    ], $overrides);
}

it('открывает прайс по правам', function (string $state, int $list, int $create): void {
    $this->actingAs(User::factory()->{$state}()->create());

    $this->get('/admin/prices')->assertStatus($list);
    $this->get('/admin/prices/create')->assertStatus($create);
})->with([
    'администратор' => ['admin', 200, 200],
    'редактор' => ['editor', 200, 200],
    'менеджер' => ['manager', 200, 403],
]);

it('сохраняет цену из рублей в копейки точно', function (): void {
    $this->actingAs(User::factory()->editor()->create());
    $service = Service::factory()->create();

    Livewire::test(CreatePriceItem::class)
        ->fillForm(priceData($service->id))
        ->call('create')
        ->assertHasNoFormErrors();

    $item = PriceItem::query()->sole();
    expect($item->price_kopecks)->toBe(250_050)
        ->and($item->unit)->toBe(PriceUnit::Piece)
        ->and($item->service_id)->toBe($service->id);
});

it('показывает в форме правки рубли и не портит копейки при сохранении', function (): void {
    $this->actingAs(User::factory()->editor()->create());
    $item = PriceItem::factory()->create(['price_kopecks' => 150_005]);

    Livewire::test(EditPriceItem::class, ['record' => $item->getRouteKey()])
        ->assertSchemaStateSet(['price_kopecks' => '1500,05'])
        ->fillForm(['title' => 'Новое название'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($item->refresh()->price_kopecks)->toBe(150_005)
        ->and($item->title)->toBe('Новое название');
});

it('валидирует каждое поле позиции прайса', function (array $overrides, string $field): void {
    $this->actingAs(User::factory()->editor()->create());
    $service = Service::factory()->create();

    Livewire::test(CreatePriceItem::class)
        ->fillForm(priceData($service->id, $overrides))
        ->call('create')
        ->assertHasFormErrors([$field]);

    expect(PriceItem::query()->count())->toBe(0);
})->with([
    'без услуги' => [['service_id' => null], 'service_id'],
    'несуществующая услуга' => [['service_id' => 999999], 'service_id'],
    'без названия' => [['title' => ''], 'title'],
    'название длиннее колонки' => [['title' => str_repeat('я', 201)], 'title'],
    'без цены' => [['price_kopecks' => ''], 'price_kopecks'],
    'нулевая цена' => [['price_kopecks' => '0'], 'price_kopecks'],
    'цена буквами' => [['price_kopecks' => 'тысяча'], 'price_kopecks'],
    'три знака копеек' => [['price_kopecks' => '100,505'], 'price_kopecks'],
    'единица не из списка' => [['unit' => 'kg'], 'unit'],
    'отрицательный порядок' => [['sort_order' => -1], 'sort_order'],
    'порядок больше smallint' => [['sort_order' => 32768], 'sort_order'],
]);
