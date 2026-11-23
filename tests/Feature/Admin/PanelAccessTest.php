<?php

declare(strict_types=1);

use App\Models\User;

it('отправляет гостя на страницу входа', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('не пускает отключённого пользователя', function (): void {
    $this->actingAs(User::factory()->admin()->inactive()->create())
        ->get('/admin')
        ->assertForbidden();
});

it('пускает активного пользователя любой роли на главную админки', function (string $state): void {
    $this->actingAs(User::factory()->{$state}()->create())
        ->get('/admin')
        ->assertOk();
})->with(['admin', 'editor', 'manager']);

it('пускает к пользователям только того, у кого есть право', function (string $state, int $status): void {
    $this->actingAs(User::factory()->{$state}()->create())
        ->get('/admin/users')
        ->assertStatus($status);
})->with([
    'администратор' => ['admin', 200],
    'редактор' => ['editor', 403],
    'менеджер' => ['manager', 403],
]);

it('у отключённого администратора нет прав, даже если роль позволяет', function (): void {
    $user = User::factory()->admin()->inactive()->make();

    expect($user->hasPermission(App\Enums\Permission::UsersManage))->toBeFalse();
});
