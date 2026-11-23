<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Портфолио для публичной части: только опубликованные объекты,
 * метки — только опубликованные услуги.
 */
final class ProjectCatalog
{
    /**
     * Объекты, где выполнялись работы направления (или его услуг).
     * Без направления — все.
     *
     * @return Collection<int, Project>
     */
    public function list(?Service $direction = null, ?int $limit = null): Collection
    {
        $query = $this->published();

        if ($direction !== null) {
            $this->withAnyService($query, $this->serviceIds($direction));
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function findPublished(string $slug): ?Project
    {
        return $this->published()->where('slug', $slug)->first();
    }

    /**
     * @return Builder<Project>
     */
    private function published(): Builder
    {
        return Project::query()
            ->published()
            ->with(['services' => function (BelongsToMany $query): void {
                /** @var BelongsToMany<Service, Project> $query */
                $query->where('services.is_published', true);
            }])
            ->ordered();
    }

    /**
     * @param  Builder<Project>  $query
     * @param  list<int>  $ids
     */
    private function withAnyService(Builder $query, array $ids): void
    {
        $query->whereHas('services', function (Builder $services) use ($ids): void {
            /** @var Builder<Service> $services */
            $services->whereIn('services.id', $ids);
        });
    }

    /**
     * Сама услуга и её опубликованные дочерние (если уже загружены).
     *
     * @return list<int>
     */
    private function serviceIds(Service $service): array
    {
        $ids = [$service->id];

        if ($service->relationLoaded('children')) {
            foreach ($service->children as $child) {
                $ids[] = $child->id;
            }
        } else {
            foreach ($service->children()->where('is_published', true)->pluck('id') as $id) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }
}
