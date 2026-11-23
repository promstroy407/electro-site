<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Предварительный расчёт: площадь × сумма ставок за 1 м².
 * Только целые копейки, без float. Итог не хранится — считается.
 */
final readonly class Estimate
{
    public const MIN_AREA = 1;

    public const MAX_AREA = 100_000;

    /**
     * @param  non-empty-list<EstimateItem>  $items
     */
    public function __construct(
        public int $areaM2,
        public array $items,
    ) {
        if ($areaM2 < self::MIN_AREA || $areaM2 > self::MAX_AREA) {
            throw new InvalidArgumentException('Площадь вне допустимых границ.');
        }
    }

    /**
     * Из снимка, сохранённого в заявке (leads.estimate_items).
     *
     * @param  array<array-key, mixed>  $items
     */
    public static function fromStored(int $areaM2, array $items): self
    {
        $parsed = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! is_string($item['title'] ?? null) || ! is_int($item['rate_kopecks'] ?? null)) {
                throw new InvalidArgumentException('Повреждённый снимок расчёта.');
            }

            $parsed[] = new EstimateItem($item['title'], $item['rate_kopecks']);
        }

        if ($parsed === []) {
            throw new InvalidArgumentException('В расчёте нет ни одной работы.');
        }

        return new self($areaM2, $parsed);
    }

    /** Сумма ставок за 1 м². */
    public function ratePerM2Kopecks(): int
    {
        return array_sum(array_map(fn (EstimateItem $item): int => $item->rateKopecks, $this->items));
    }

    public function totalKopecks(): int
    {
        return $this->areaM2 * $this->ratePerM2Kopecks();
    }

    /**
     * Снимок для колонки leads.estimate_items.
     *
     * @return list<array{title: string, rate_kopecks: int}>
     */
    public function itemsForStorage(): array
    {
        return array_map(
            fn (EstimateItem $item): array => ['title' => $item->title, 'rate_kopecks' => $item->rateKopecks],
            $this->items,
        );
    }
}
