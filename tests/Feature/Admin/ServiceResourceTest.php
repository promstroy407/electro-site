<?php

declare(strict_types=1);

use App\Enums\Audience;
use App\Enums\ServiceIcon;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * @return array<string, mixed>
 */
function validServiceData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Монтаж СКУД',
        'slug' => 'montazh-skud',
        'parent_id' => null,
        'audience' => Audience::Business->value,
        'sort_order' => 10,
        'is_published' => true,
        'summary' => 'Кратко об услуге',
        'body' => 'Подробное описание',
        'meta_title' => null,
        'meta_description' => null,
        'icon' => ServiceIcon::Video->value,
    ], $overrides);
}

describe('доступ', function (): void {
    it('открывает список услуг тем, кому можно смотреть', function (string $state, int $status): void {
        $this->actingAs(User::factory()->{$state}()->create())
            ->get('/admin/services')
            ->assertStatus($status);
    })->with([
        'администратор' => ['admin', 200],
        'редактор' => ['editor', 200],
        'менеджер' => ['manager', 200],
    ]);

    it('пускает создавать и править только с правом редактирования', function (string $state, int $status): void {
        $service = Service::factory()->create();
        $this->actingAs(User::factory()->{$state}()->create());

        $this->get('/admin/services/create')->assertStatus($status);
        $this->get('/admin/services/'.$service->id.'/edit')->assertStatus($status);
    })->with([
        'администратор' => ['admin', 200],
        'редактор' => ['editor', 200],
        'менеджер' => ['manager', 403],
    ]);

    it('даёт менеджеру карточку услуги только на просмотр', function (): void {
        $service = Service::factory()->create();

        $this->actingAs(User::factory()->manager()->create())
            ->get('/admin/services/'.$service->id)
            ->assertOk();
    });

    it('не даёт удалить направление, в котором есть услуги', function (): void {
        $editor = User::factory()->editor()->create();
        $direction = Service::factory()->create();
        $child = Service::factory()->childOf($direction)->create();

        expect($editor->can('delete', $direction))->toBeFalse()
            ->and($editor->can('delete', $child))->toBeTrue();
    });
});

describe('создание и правка', function (): void {
    beforeEach(function (): void {
        $this->actingAs(User::factory()->editor()->create());
    });

    it('создаёт услугу внутри направления', function (): void {
        $direction = Service::factory()->create();

        Livewire::test(CreateService::class)
            ->fillForm(validServiceData(['parent_id' => $direction->id]))
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::query()->where('slug', 'montazh-skud')->firstOrFail();
        expect($service->parent_id)->toBe($direction->id)
            ->and($service->audience)->toBe(Audience::Business)
            ->and($service->icon)->toBe(ServiceIcon::Video)
            ->and($service->is_published)->toBeTrue();
    });

    it('подставляет адрес из названия у новой услуги', function (): void {
        Livewire::test(CreateService::class)
            ->fillForm(['title' => 'Монтаж видеонаблюдения'])
            ->assertFormSet(['slug' => Str::slug('Монтаж видеонаблюдения')]);

        expect(Str::slug('Монтаж видеонаблюдения'))->toMatch(ServiceForm::SLUG_PATTERN);
    });

    it('валидирует каждое поле по схеме таблицы', function (array $overrides, array $errors): void {
        Service::factory()->create(['slug' => 'zanyatyy-adres']);

        Livewire::test(CreateService::class)
            ->fillForm(validServiceData($overrides))
            ->call('create')
            ->assertHasFormErrors($errors);
    })->with([
        'пустое название' => [['title' => ''], ['title' => 'required']],
        'название длиннее колонки' => [['title' => str_repeat('я', 151)], ['title' => 'max']],
        'пустой адрес' => [['slug' => ''], ['slug' => 'required']],
        'кириллица в адресе' => [['slug' => 'монтаж'], ['slug' => 'regex']],
        'заглавные в адресе' => [['slug' => 'Montazh'], ['slug' => 'regex']],
        'двойной дефис' => [['slug' => 'montazh--skud'], ['slug' => 'regex']],
        'адрес длиннее колонки' => [['slug' => str_repeat('a', 161)], ['slug' => 'max']],
        'занятый адрес' => [['slug' => 'zanyatyy-adres'], ['slug' => 'unique']],
        'аудитория не из списка' => [['audience' => 'vip'], ['audience']],
        'иконка не из списка' => [['icon' => 'rocket'], ['icon']],
        'без аудитории' => [['audience' => null], ['audience' => 'required']],
        'отрицательный порядок' => [['sort_order' => -1], ['sort_order' => 'min']],
        'порядок больше smallint' => [['sort_order' => 32768], ['sort_order' => 'max']],
        'дробный порядок' => [['sort_order' => 1.5], ['sort_order' => 'integer']],
        'кратко длиннее колонки' => [['summary' => str_repeat('я', 301)], ['summary' => 'max']],
        'заголовок SEO длиннее колонки' => [['meta_title' => str_repeat('я', 71)], ['meta_title' => 'max']],
        'описание SEO длиннее колонки' => [['meta_description' => str_repeat('я', 161)], ['meta_description' => 'max']],
        'несуществующее направление' => [['parent_id' => 999999], ['parent_id']],
    ]);

    it('не даёт вложить услугу глубже второго уровня', function (): void {
        $direction = Service::factory()->create();
        $child = Service::factory()->childOf($direction)->create();

        Livewire::test(CreateService::class)
            ->fillForm(validServiceData(['parent_id' => $child->id]))
            ->call('create')
            ->assertHasFormErrors(['parent_id']);
    });

    it('не даёт сделать направление с услугами вложенным', function (): void {
        $direction = Service::factory()->create();
        Service::factory()->childOf($direction)->create();
        $otherDirection = Service::factory()->create();

        Livewire::test(EditService::class, ['record' => $direction->getRouteKey()])
            ->fillForm(['parent_id' => $otherDirection->id])
            ->call('save')
            ->assertHasFormErrors(['parent_id']);
    });

    it('сохраняет правку и оставляет свой же адрес допустимым', function (): void {
        $service = Service::factory()->create(['slug' => 'svoy-adres']);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm(['title' => 'Новое название', 'slug' => 'svoy-adres'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($service->refresh()->title)->toBe('Новое название');
    });
});
