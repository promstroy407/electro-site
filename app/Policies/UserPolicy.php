<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::UsersManage);
    }

    public function view(User $actor, User $user): bool
    {
        return $actor->hasPermission(Permission::UsersManage);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::UsersManage);
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->hasPermission(Permission::UsersManage);
    }

    /** Себя удалить нельзя: иначе можно остаться без администратора. */
    public function delete(User $actor, User $user): bool
    {
        return $actor->hasPermission(Permission::UsersManage) && $actor->id !== $user->id;
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
