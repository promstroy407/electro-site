<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Деньги: в БД копейки целым числом, людям — рубли.
 * Только целочисленная арифметика, никакого float.
 */
final class RubleFormatter
{
    private const NBSP = "\u{00A0}";

    /** Максимум 999 999 999,99 ₽ — с запасом умещается в bigint. */
    private const PATTERN = '/^(\d{1,9})(?:[.,](\d{1,2}))?$/';

    /** «1 500 ₽», «1 500,50 ₽» — с неразрывными пробелами. */
    public function format(int $kopecks): string
    {
        return $this->amount($kopecks, self::NBSP).self::NBSP.'₽';
    }

    /** Значение для поля ввода: «1500», «1500,50». */
    public function toInput(int $kopecks): string
    {
        return $this->amount($kopecks, '');
    }

    /** Для машин (микроразметка schema.org): «1500», «1500.50» — точка, без пробелов. */
    public function toDecimal(int $kopecks): string
    {
        return str_replace(',', '.', $this->amount($kopecks, ''));
    }

    /**
     * Рубли из поля ввода в копейки: «1 500,5» → 150050.
     * null — если строка не похожа на сумму.
     */
    public function parse(string $input): ?int
    {
        $clean = str_replace([' ', self::NBSP, "\u{202F}"], '', trim($input));

        if (preg_match(self::PATTERN, $clean, $m) !== 1) {
            return null;
        }

        $kopecks = isset($m[2]) ? (int) str_pad($m[2], 2, '0') : 0;

        return (int) $m[1] * 100 + $kopecks;
    }

    private function amount(int $kopecks, string $thousands): string
    {
        $sign = $kopecks < 0 ? '-' : '';
        $abs = abs($kopecks);
        $rubles = number_format(intdiv($abs, 100), 0, '', $thousands);
        $rest = $abs % 100;

        return $sign.$rubles.($rest > 0 ? ','.str_pad((string) $rest, 2, '0', STR_PAD_LEFT) : '');
    }
}
