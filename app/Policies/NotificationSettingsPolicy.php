<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\NotificationSettings;
use App\Models\User;

/** Настройки — одна запись: её можно только смотреть и править. */
final class NotificationSettingsPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::SettingsManage);
    }

    public function view(User $actor, NotificationSettings $settings): bool
    {
        return $actor->hasPermission(Permission::SettingsManage);
    }

    public function create(User $actor): bool
    {
        return false;
    }

    public function update(User $actor, NotificationSettings $settings): bool
    {
        return $actor->hasPermission(Permission::SettingsManage);
    }

    public function delete(User $actor, NotificationSettings $settings): bool
    {
        return false;
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
