<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Collection;

/**
 * Партнёры для публичной части: только опубликованные, по порядку из админки.
 */
final class PartnerCatalog
{
    /**
     * @return Collection<int, Partner>
     */
    public function list(?int $limit = null): Collection
    {
        $query = Partner::query()->published()->ordered();

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
