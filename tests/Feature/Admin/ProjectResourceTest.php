<?php

declare(strict_types=1);

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function projectData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Склад 1200 м²',
        'slug' => 'sklad-1200',
        'city' => 'Йошкар-Ола',
        'year' => 2025,
        'services' => [],
        'is_published' => true,
        'sort_order' => 0,
        'summary' => 'Кратко',
        'body' => 'Подробно',
    ], $overrides);
}

beforeEach(function (): void {
    Storage::fake('public');
});

it('открывает объекты по правам', function (string $state, int $list, int $create): void {
    $this->actingAs(User::factory()->{$state}()->create());

    $this->get('/admin/projects')->assertStatus($list);
    $this->get('/admin/projects/create')->assertStatus($create);
})->with([
    'администратор' => ['admin', 200, 200],
    'редактор' => ['editor', 200, 200],
    'менеджер' => ['manager', 200, 403],
]);

it('создаёт объект с фото и метками услуг', function (): void {
    $this->actingAs(User::factory()->editor()->create());
    $service = Service::factory()->create();

    Livewire::test(CreateProject::class)
        ->fillForm(projectData([
            'services' => [$service->id],
            'photos' => [UploadedFile::fake()->image('shchit.jpg', 1200, 900)],
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    $project = Project::query()->with('services')->sole();
    expect($project->photos)->toHaveCount(1)
        ->and($project->photos[0])->toStartWith('portfolio/')
        ->and($project->services->pluck('id')->all())->toBe([$service->id]);
    Storage::disk('public')->assertExists($project->photos[0]);
});

it('валидирует каждое поле объекта', function (array $overrides, string $field): void {
    $this->actingAs(User::factory()->editor()->create());
    Project::factory()->create(['slug' => 'zanyat']);

    Livewire::test(CreateProject::class)
        ->fillForm(projectData($overrides))
        ->call('create')
        ->assertHasFormErrors([$field]);
})->with([
    'без названия' => [['title' => ''], 'title'],
    'название длиннее колонки' => [['title' => str_repeat('я', 151)], 'title'],
    'кириллица в адресе' => [['slug' => 'склад'], 'slug'],
    'занятый адрес' => [['slug' => 'zanyat'], 'slug'],
    'без города' => [['city' => ''], 'city'],
    'город длиннее колонки' => [['city' => str_repeat('я', 101)], 'city'],
    'год раньше 2000' => [['year' => 1999], 'year'],
    'год из будущего' => [['year' => 2100], 'year'],
    'кратко длиннее колонки' => [['summary' => str_repeat('я', 301)], 'summary'],
    'отрицательный порядок' => [['sort_order' => -1], 'sort_order'],
    'не картинка' => [['photos' => [UploadedFile::fake()->create('smeta.pdf', 100, 'application/pdf')]], 'photos'],
    'слишком большое фото' => [['photos' => [UploadedFile::fake()->image('big.jpg')->size(10241)]], 'photos'],
]);
