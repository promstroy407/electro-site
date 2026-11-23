<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\GalleryPhoto;
use App\Models\User;

final class GalleryPhotoPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::GalleryView);
    }

    public function view(User $actor, GalleryPhoto $photo): bool
    {
        return $actor->hasPermission(Permission::GalleryView);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::GalleryManage);
    }

    public function update(User $actor, GalleryPhoto $photo): bool
    {
        return $actor->hasPermission(Permission::GalleryManage);
    }

    public function delete(User $actor, GalleryPhoto $photo): bool
    {
        return $actor->hasPermission(Permission::GalleryManage);
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }

    public function reorder(User $actor): bool
    {
        return $actor->hasPermission(Permission::GalleryManage);
    }
}
