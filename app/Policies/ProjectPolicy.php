<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Project;
use App\Models\User;

final class ProjectPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::PortfolioView);
    }

    public function view(User $actor, Project $project): bool
    {
        return $actor->hasPermission(Permission::PortfolioView);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::PortfolioManage);
    }

    public function update(User $actor, Project $project): bool
    {
        return $actor->hasPermission(Permission::PortfolioManage);
    }

    public function delete(User $actor, Project $project): bool
    {
        return $actor->hasPermission(Permission::PortfolioManage);
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
