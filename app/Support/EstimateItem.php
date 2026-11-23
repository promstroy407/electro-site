<?php

declare(strict_types=1);

namespace App\Support;

/** Одна отмеченная работа в расчёте: название и цена за 1 м² в копейках. */
final readonly class EstimateItem
{
    public function __construct(
        public string $title,
        public int $rateKopecks,
    ) {}
}
