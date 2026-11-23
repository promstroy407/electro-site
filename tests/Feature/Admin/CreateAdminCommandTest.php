<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

it('создаёт администратора и один раз печатает рабочий пароль', function (): void {
    $code = Artisan::call('admin:create', ['email' => 'owner@example.test', '--name' => 'Владелец']);
    $output = Artisan::output();

    expect($code)->toBe(0)
        ->and(preg_match('/Пароль: (\S+)/u', $output, $match))->toBe(1);

    $user = User::query()->where('email', 'owner@example.test')->sole();

    expect($user->role)->toBe(Role::Admin)
        ->and($user->is_active)->toBeTrue()
        ->and($user->name)->toBe('Владелец')
        ->and(Hash::check($match[1], $user->password))->toBeTrue();
});

it('не создаёт второго пользователя с той же почтой', function (): void {
    User::factory()->create(['email' => 'owner@example.test']);

    $this->artisan('admin:create', ['email' => 'owner@example.test'])->assertFailed();

    expect(User::query()->where('email', 'owner@example.test')->count())->toBe(1);
});

it('отклоняет неверную почту и слишком длинное имя', function (string $email, string $name): void {
    $this->artisan('admin:create', ['email' => $email, '--name' => $name])->assertFailed();

    expect(User::query()->count())->toBe(0);
})->with([
    'не почта' => ['owner', 'Владелец'],
    'имя длиннее колонки' => ['owner@example.test', str_repeat('я', 101)],
]);
