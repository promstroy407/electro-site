<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('создаёт пользователя с ролью и хеширует пароль', function (): void {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.test',
            'role' => Role::Editor->value,
            'is_active' => true,
            'password' => 'длинный-пароль-123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'ivan@example.test')->firstOrFail();
    expect($user->role)->toBe(Role::Editor)
        ->and(Hash::check('длинный-пароль-123', $user->password))->toBeTrue();
});

it('валидирует поля пользователя', function (array $data, array $errors): void {
    User::factory()->create(['email' => 'taken@example.test']);

    Livewire::test(CreateUser::class)
        ->fillForm(array_merge([
            'name' => 'Иван',
            'email' => 'new@example.test',
            'role' => Role::Manager->value,
            'password' => 'длинный-пароль-123',
        ], $data))
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    'пустое имя' => [['name' => ''], ['name' => 'required']],
    'имя длиннее колонки' => [['name' => str_repeat('я', 101)], ['name' => 'max']],
    'кривой email' => [['email' => 'не-почта'], ['email' => 'email']],
    'занятый email' => [['email' => 'taken@example.test'], ['email' => 'unique']],
    'роль не из списка' => [['role' => 'superuser'], ['role']],
    'короткий пароль' => [['password' => 'короткий'], ['password' => 'min']],
    'без пароля' => [['password' => ''], ['password' => 'required']],
]);

it('не меняет пароль, если при редактировании поле пустое', function (): void {
    $user = User::factory()->create(['password' => 'старый-пароль-123']);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm(['name' => 'Новое имя', 'password' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->name)->toBe('Новое имя')
        ->and(Hash::check('старый-пароль-123', $user->password))->toBeTrue();
});

it('не даёт удалить самого себя', function (): void {
    expect($this->admin->can('delete', $this->admin))->toBeFalse()
        ->and($this->admin->can('delete', User::factory()->create()))->toBeTrue();
});
