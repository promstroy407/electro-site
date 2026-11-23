<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Service;
use App\Models\User;

final class ServicePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::ServicesView);
    }

    public function view(User $actor, Service $service): bool
    {
        return $actor->hasPermission(Permission::ServicesView);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::ServicesManage);
    }

    public function update(User $actor, Service $service): bool
    {
        return $actor->hasPermission(Permission::ServicesManage);
    }

    /** Направление с услугами внутри удалить нельзя: сначала их перенести или удалить. */
    public function delete(User $actor, Service $service): bool
    {
        return $actor->hasPermission(Permission::ServicesManage)
            && ! $service->children()->exists();
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
