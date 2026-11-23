<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;

/**
 * Дерево услуг для админки: что можно выбрать родителем.
 */
final class ServiceTree
{
    /**
     * Направления верхнего уровня, кроме самой редактируемой услуги.
     *
     * @return array<int, string>
     */
    public function parentOptions(?Service $except = null): array
    {
        $query = Service::query()->directions()->ordered();

        if ($except !== null && $except->exists) {
            $query->whereKeyNot($except->id);
        }

        /** @var array<int, string> $options */
        $options = $query->pluck('title', 'id')->all();

        return $options;
    }

    /**
     * Все услуги для выбора в прайсе, сгруппированные по направлениям:
     * [«Видеонаблюдение» => [id => «Видеонаблюдение — в целом», id => «Для дома»]].
     *
     * @return array<string, array<int, string>>
     */
    public function groupedOptions(): array
    {
        $groups = [];
        $directions = Service::query()->directions()->ordered()->with('children')->get();

        foreach ($directions as $direction) {
            $options = [$direction->id => $direction->title.' — в целом'];
            foreach ($direction->children as $child) {
                $options[$child->id] = $child->title;
            }
            $groups[$direction->title] = $options;
        }

        return $groups;
    }
}
