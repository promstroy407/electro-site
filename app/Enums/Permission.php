<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Права в админке. Новая сущность — новые права здесь
 * и их раздача по ролям в Role::permissions().
 */
enum Permission: string
{
    case UsersManage = 'users.manage';
    case SettingsManage = 'settings.manage';

    case ServicesView = 'services.view';
    case ServicesManage = 'services.manage';

    case PricesView = 'prices.view';
    case PricesManage = 'prices.manage';

    case PortfolioView = 'portfolio.view';
    case PortfolioManage = 'portfolio.manage';

    case GalleryView = 'gallery.view';
    case GalleryManage = 'gallery.manage';

    case PartnersView = 'partners.view';
    case PartnersManage = 'partners.manage';

    case LeadsView = 'leads.view';
    case LeadsManage = 'leads.manage';
    case LeadsDelete = 'leads.delete';

    public function label(): string
    {
        return match ($this) {
            self::UsersManage => 'Управление пользователями',
            self::SettingsManage => 'Настройки сайта и уведомлений',
            self::ServicesView => 'Просмотр услуг',
            self::ServicesManage => 'Редактирование услуг',
            self::PricesView => 'Просмотр прайса',
            self::PricesManage => 'Редактирование прайса',
            self::PortfolioView => 'Просмотр портфолио',
            self::PortfolioManage => 'Редактирование портфолио',
            self::GalleryView => 'Просмотр фотогалереи',
            self::GalleryManage => 'Редактирование фотогалереи',
            self::PartnersView => 'Просмотр партнёров',
            self::PartnersManage => 'Редактирование партнёров',
            self::LeadsView => 'Просмотр заявок',
            self::LeadsManage => 'Обработка заявок',
            self::LeadsDelete => 'Удаление заявок',
        };
    }
}
