<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Audience;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Каталог услуг для публичной части: только опубликованное,
 * связи грузятся явно (ленивая загрузка вне прода запрещена).
 */
final class ServiceCatalog
{
    /**
     * Опубликованные направления с опубликованными услугами внутри.
     *
     * @return Collection<int, Service>
     */
    public function directions(Audience $audience = Audience::All): Collection
    {
        return Service::query()
            ->directions()
            ->published()
            ->forAudience($audience)
            ->with(['children' => function (HasMany $query) use ($audience): void {
                /** @var HasMany<Service, Service> $query */
                $query->where('is_published', true)
                    ->whereIn('audience', $audience->matchingValues());
            }])
            ->ordered()
            ->get();
    }

    /**
     * Опубликованная услуга по адресу. Услуга внутри неопубликованного
     * направления тоже скрыта: снятое с публикации направление прячет всё.
     */
    public function findPublished(string $slug): ?Service
    {
        return Service::query()
            ->published()
            ->where('slug', $slug)
            ->where(function (Builder $query): void {
                /** @var Builder<Service> $query */
                $query->whereNull('parent_id')
                    ->orWhereHas('parent', function (Builder $parent): void {
                        /** @var Builder<Service> $parent */
                        $parent->where('is_published', true);
                    });
            })
            ->with([
                'parent',
                'children' => function (HasMany $query): void {
                    /** @var HasMany<Service, Service> $query */
                    $query->where('is_published', true);
                },
            ])
            ->first();
    }
}
