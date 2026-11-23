<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PriceUnit;
use App\Models\PriceItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Прайс для публичной части: только опубликованные позиции
 * опубликованных услуг. «Цена от» считается, а не хранится.
 */
final readonly class PriceCatalog
{
    public function __construct(
        private ServiceCatalog $services,
    ) {}

    /**
     * «Цена от» по каждой услуге одним запросом; у направления —
     * по нему самому и его опубликованным услугам. Берётся минимум среди
     * цен за штуку, точку, комплект и т. п.; цены за метр — только если
     * других нет.
     *
     * @param  Collection<int, Service>  $directions  направления с загруженными children
     * @return array<int, int> id услуги => копейки
     */
    public function pricesFrom(Collection $directions): array
    {
        $ids = [];
        foreach ($directions as $direction) {
            $ids[] = $direction->id;
            foreach ($direction->children as $child) {
                $ids[] = $child->id;
            }
        }

        if ($ids === []) {
            return [];
        }

        $rows = PriceItem::query()
            ->where('is_published', true)
            ->whereIn('service_id', $ids)
            ->groupBy('service_id')
            ->selectRaw('service_id, MIN(price_kopecks) AS min_all, MIN(CASE WHEN unit <> ? THEN price_kopecks END) AS min_whole', [PriceUnit::Meter->value])
            ->toBase()
            ->get();

        $all = [];
        $whole = [];
        foreach ($rows as $row) {
            $all[(int) $row->service_id] = (int) $row->min_all;
            if ($row->min_whole !== null) {
                $whole[(int) $row->service_id] = (int) $row->min_whole;
            }
        }

        $result = [];
        foreach ($directions as $direction) {
            foreach ([$direction, ...$direction->children] as $service) {
                $value = $this->pick([$service], $whole, $all);
                if ($value !== null) {
                    $result[$service->id] = $value;
                }
            }

            $value = $this->pick([$direction, ...$direction->children], $whole, $all);
            if ($value !== null) {
                $result[$direction->id] = $value;
            }
        }

        return $result;
    }

    /**
     * @param  list<Service>  $services
     * @param  array<int, int>  $whole
     * @param  array<int, int>  $all
     */
    private function pick(array $services, array $whole, array $all): ?int
    {
        foreach ([$whole, $all] as $source) {
            $candidates = [];
            foreach ($services as $service) {
                if (isset($source[$service->id])) {
                    $candidates[] = $source[$service->id];
                }
            }
            if ($candidates !== []) {
                return min($candidates);
            }
        }

        return null;
    }

    /**
     * Догружает позиции прайса в уже найденную услугу и её
     * опубликованные дочерние услуги (не перезагружая children).
     */
    public function loadInto(Service $service): void
    {
        $published = function (HasMany $query): void {
            /** @var HasMany<PriceItem, Service> $query */
            $query->where('is_published', true);
        };

        $service->load(['priceItems' => $published]);
        $service->children->load(['priceItems' => $published]);
    }

    /** «Цена от» по уже загруженным позициям услуги и её детей (правило — как в pricesFrom). */
    public function priceFromLoaded(Service $service): ?int
    {
        $whole = [];
        $all = [];
        foreach ([$service, ...$service->children] as $item) {
            foreach ($item->priceItems as $price) {
                $all[] = $price->price_kopecks;
                if ($price->unit->suitsPriceFrom()) {
                    $whole[] = $price->price_kopecks;
                }
            }
        }

        if ($whole !== []) {
            return min($whole);
        }

        return $all === [] ? null : min($all);
    }

    /**
     * Весь прайс для страницы «Цены»: направления → услуги → позиции.
     *
     * @return Collection<int, Service>
     */
    public function fullList(): Collection
    {
        $directions = $this->services->directions();

        foreach ($directions as $direction) {
            $this->loadInto($direction);
        }

        return $directions;
    }
}
