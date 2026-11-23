<?php

declare(strict_types=1);

use App\Enums\Permission;
use App\Enums\Role;

it('даёт администратору все права', function (): void {
    foreach (Permission::cases() as $permission) {
        expect(Role::Admin->allows($permission))->toBeTrue();
    }
});

it('раздаёт права по ролям по матрице', function (Role $role, Permission $permission, bool $expected): void {
    expect($role->allows($permission))->toBe($expected);
})->with([
    'редактор правит услуги' => [Role::Editor, Permission::ServicesManage, true],
    'редактор не управляет людьми' => [Role::Editor, Permission::UsersManage, false],
    'менеджер видит услуги' => [Role::Manager, Permission::ServicesView, true],
    'менеджер не правит услуги' => [Role::Manager, Permission::ServicesManage, false],
    'менеджер не управляет людьми' => [Role::Manager, Permission::UsersManage, false],
    'менеджер обрабатывает заявки' => [Role::Manager, Permission::LeadsManage, true],
    'менеджер не удаляет заявки' => [Role::Manager, Permission::LeadsDelete, false],
    'менеджер не меняет настройки' => [Role::Manager, Permission::SettingsManage, false],
    'редактор не видит заявки' => [Role::Editor, Permission::LeadsView, false],
    'редактор правит прайс' => [Role::Editor, Permission::PricesManage, true],
    'менеджер видит прайс' => [Role::Manager, Permission::PricesView, true],
    'менеджер не правит прайс' => [Role::Manager, Permission::PricesManage, false],
    'редактор правит портфолио' => [Role::Editor, Permission::PortfolioManage, true],
    'менеджер не правит портфолио' => [Role::Manager, Permission::PortfolioManage, false],
    'редактор правит галерею' => [Role::Editor, Permission::GalleryManage, true],
    'менеджер видит галерею' => [Role::Manager, Permission::GalleryView, true],
    'менеджер не правит галерею' => [Role::Manager, Permission::GalleryManage, false],
    'редактор правит партнёров' => [Role::Editor, Permission::PartnersManage, true],
    'менеджер видит партнёров' => [Role::Manager, Permission::PartnersView, true],
    'менеджер не правит партнёров' => [Role::Manager, Permission::PartnersManage, false],
]);

it('у каждой роли и права есть русская подпись', function (): void {
    foreach (Role::cases() as $role) {
        expect($role->label())->not->toBe('');
    }
    foreach (Permission::cases() as $permission) {
        expect($permission->label())->not->toBe('');
    }
    expect(Role::options())->toHaveCount(count(Role::cases()));
});
