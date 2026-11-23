<?php

declare(strict_types=1);

use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function partnerData(array $overrides = []): array
{
    return array_merge([
        'name' => 'СК М ГРУПП',
        'url' => 'https://sk-m-group.ru',
        'description' => 'Строительная компания, Йошкар-Ола',
        'logo_path' => UploadedFile::fake()->image('logo.png', 300, 120),
        'is_published' => true,
        'sort_order' => 0,
    ], $overrides);
}

beforeEach(function (): void {
    Storage::fake('public');
});

it('открывает партнёров по правам', function (string $state, int $list, int $create): void {
    $this->actingAs(User::factory()->{$state}()->create());

    $this->get('/admin/partnery')->assertStatus($list);
    $this->get('/admin/partnery/create')->assertStatus($create);
})->with([
    'администратор' => ['admin', 200, 200],
    'редактор' => ['editor', 200, 200],
    'менеджер' => ['manager', 200, 403],
]);

it('не пускает менеджера править партнёра по прямой ссылке', function (): void {
    $this->actingAs(User::factory()->manager()->create());
    $partner = Partner::factory()->create();

    $this->get('/admin/partnery/'.$partner->id.'/edit')->assertForbidden();
});

it('добавляет партнёра с логотипом в папку partners', function (): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(CreatePartner::class)
        ->fillForm(partnerData())
        ->call('create')
        ->assertHasNoFormErrors();

    $partner = Partner::query()->sole();
    expect($partner->name)->toBe('СК М ГРУПП')
        ->and($partner->url)->toBe('https://sk-m-group.ru')
        ->and($partner->logo_path)->toStartWith('partners/');
    Storage::disk('public')->assertExists((string) $partner->logo_path);
});

it('добавляет партнёра без логотипа и без сайта', function (): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(CreatePartner::class)
        ->fillForm(partnerData(['logo_path' => null, 'url' => null, 'description' => null]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Partner::query()->sole()->logo_path)->toBeNull();
});

it('валидирует каждое поле партнёра', function (array $overrides, string $field): void {
    $this->actingAs(User::factory()->editor()->create());

    Livewire::test(CreatePartner::class)
        ->fillForm(partnerData($overrides))
        ->call('create')
        ->assertHasFormErrors([$field]);
})->with([
    'без названия' => [['name' => ''], 'name'],
    'название длиннее колонки' => [['name' => str_repeat('я', 101)], 'name'],
    'сайт не адрес' => [['url' => 'не сайт'], 'url'],
    'сайт не http' => [['url' => 'ftp://sk-m-group.ru'], 'url'],
    'сайт длиннее колонки' => [['url' => 'https://'.str_repeat('a', 250).'.ru'], 'url'],
    'описание длиннее колонки' => [['description' => str_repeat('я', 301)], 'description'],
    'логотип не картинка' => [['logo_path' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf')], 'logo_path'],
    'логотип больше 2 МБ' => [['logo_path' => UploadedFile::fake()->image('logo.png')->size(2049)], 'logo_path'],
    'отрицательный порядок' => [['sort_order' => -1], 'sort_order'],
]);
