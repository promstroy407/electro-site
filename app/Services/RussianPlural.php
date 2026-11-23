<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Склонение существительного после числа: 1 услуга, 3 услуги, 5 услуг,
 * 11 услуг, 21 услуга.
 */
final class RussianPlural
{
    public function choose(int $count, string $one, string $few, string $many): string
    {
        $n = abs($count) % 100;

        if ($n >= 11 && $n <= 14) {
            return $many;
        }

        return match ($n % 10) {
            1 => $one,
            2, 3, 4 => $few,
            default => $many,
        };
    }
}
