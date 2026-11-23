<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\PriceItem;
use App\Models\User;

final class PriceItemPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::PricesView);
    }

    public function view(User $actor, PriceItem $item): bool
    {
        return $actor->hasPermission(Permission::PricesView);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::PricesManage);
    }

    public function update(User $actor, PriceItem $item): bool
    {
        return $actor->hasPermission(Permission::PricesManage);
    }

    public function delete(User $actor, PriceItem $item): bool
    {
        return $actor->hasPermission(Permission::PricesManage);
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
