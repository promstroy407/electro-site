<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CalculatorRate;
use App\Models\User;

/**
 * Ставки калькулятора — часть цен, поэтому те же права, что у прайса:
 * кто правит прайс, тот правит и ставки за м².
 */
final class CalculatorRatePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::PricesView);
    }

    public function view(User $actor, CalculatorRate $rate): bool
    {
        return $actor->hasPermission(Permission::PricesView);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::PricesManage);
    }

    public function update(User $actor, CalculatorRate $rate): bool
    {
        return $actor->hasPermission(Permission::PricesManage);
    }

    public function delete(User $actor, CalculatorRate $rate): bool
    {
        return $actor->hasPermission(Permission::PricesManage);
    }

    public function deleteAny(User $actor): bool
    {
        return false;
    }
}
