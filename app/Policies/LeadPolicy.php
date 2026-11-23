<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Lead;
use App\Models\User;

final class LeadPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::LeadsView);
    }

    public function view(User $actor, Lead $lead): bool
    {
        return $actor->hasPermission(Permission::LeadsView);
    }

    /** Заявки приходят только с сайта, руками их не создают. */
    public function create(User $actor): bool
    {
        return false;
    }

    public function update(User $actor, Lead $lead): bool
    {
        return $actor->hasPermission(Permission::LeadsManage);
    }

    public function delete(User $actor, Lead $lead): bool
    {
        return $actor->hasPermission(Permission::LeadsDelete);
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
