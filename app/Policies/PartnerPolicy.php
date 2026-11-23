<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Partner;
use App\Models\User;

final class PartnerPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::PartnersView);
    }

    public function view(User $actor, Partner $partner): bool
    {
        return $actor->hasPermission(Permission::PartnersView);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::PartnersManage);
    }

    public function update(User $actor, Partner $partner): bool
    {
        return $actor->hasPermission(Permission::PartnersManage);
    }

    public function delete(User $actor, Partner $partner): bool
    {
        return $actor->hasPermission(Permission::PartnersManage);
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }

    public function reorder(User $actor): bool
    {
        return $actor->hasPermission(Permission::PartnersManage);
    }
}
