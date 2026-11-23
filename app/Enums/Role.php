<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Роли пользователей админки. Набор прав каждой роли задаётся здесь,
 * в одном месте; политики спрашивают только про права, не про роли.
 */
enum Role: string
{
    /** Владелец: всё, включая пользователей. */
    case Admin = 'admin';

    /** Контент: услуги, портфолио, прайс, отзывы. */
    case Editor = 'editor';

    /** Менеджер: обработка заявок, контент только смотрит. */
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Администратор',
            self::Editor => 'Редактор',
            self::Manager => 'Менеджер',
        };
    }

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::Editor => [
                Permission::ServicesView,
                Permission::ServicesManage,
                Permission::PricesView,
                Permission::PricesManage,
                Permission::PortfolioView,
                Permission::PortfolioManage,
                Permission::GalleryView,
                Permission::GalleryManage,
                Permission::PartnersView,
                Permission::PartnersManage,
            ],
            self::Manager => [
                Permission::ServicesView,
                Permission::PricesView,
                Permission::PortfolioView,
                Permission::GalleryView,
                Permission::PartnersView,
                Permission::LeadsView,
                Permission::LeadsManage,
            ],
        };
    }

    public function allows(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    /**
     * Варианты для выпадающего списка.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $role) {
            $options[$role->value] = $role->label();
        }

        return $options;
    }
}
