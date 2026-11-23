<?php

declare(strict_types=1);

use App\Filament\Resources\CalculatorRates\Pages\CreateCalculatorRate;
use App\Filament\Resources\CalculatorRates\Pages\EditCalculatorRate;
use App\Models\CalculatorRate;
use App\Models\User;
use Livewire\Livewire;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function rateData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Видеонаблюдение',
        'rate_kopecks' => '500,50',
        'is_default' => false,
        'is_published' => true,
        'sort_order' => 20,
    ], $overrides);
}

it('открывает ставки калькулятора по правам прайса', function (string $state, int $list, int $create): void {
    $this->actingAs(User::factory()->{$state}()->create());

    $this->get('/admin/calculator')->assertStatus($list);
    $this->get('/admin/calculator/create')->assertStatus($create);
})->with([
    'администратор' => ['admin', 200, 200],
    'редактор' => ['editor', 200, 200],
    'менеджер' => ['manager', 200, 403],
]);

it('сохраняет ставку из рублей в копейки точно', function (): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(CreateCalculatorRate::class)
        ->fillForm(rateData())
        ->call('create')
        ->assertHasNoFormErrors();

    expect(CalculatorRate::query()->sole()->rate_kopecks)->toBe(50_050);
});

it('показывает в правке рубли и не портит копейки при сохранении', function (): void {
    $this->actingAs(User::factory()->editor()->create());
    $rate = CalculatorRate::factory()->create(['rate_kopecks' => 150_005]);

    Livewire::test(EditCalculatorRate::class, ['record' => $rate->getRouteKey()])
        ->assertSchemaStateSet(['rate_kopecks' => '1500,05'])
        ->fillForm(['title' => 'Электрика'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($rate->refresh()->rate_kopecks)->toBe(150_005)
        ->and($rate->title)->toBe('Электрика');
});

it('валидирует каждое поле ставки', function (array $overrides, string $field): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(CreateCalculatorRate::class)
        ->fillForm(rateData($overrides))
        ->call('create')
        ->assertHasFormErrors([$field]);

    expect(CalculatorRate::query()->count())->toBe(0);
})->with([
    'без названия' => [['title' => ''], 'title'],
    'название длиннее колонки' => [['title' => str_repeat('я', 101)], 'title'],
    'без ставки' => [['rate_kopecks' => ''], 'rate_kopecks'],
    'нулевая ставка' => [['rate_kopecks' => '0'], 'rate_kopecks'],
    'ставка буквами' => [['rate_kopecks' => 'пятьсот'], 'rate_kopecks'],
    'отрицательный порядок' => [['sort_order' => -1], 'sort_order'],
    'порядок больше smallint' => [['sort_order' => 32768], 'sort_order'],
]);
