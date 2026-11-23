<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Приводит телефон к виду +XXXXXXXXXXX, чтобы один и тот же номер,
 * введённый по-разному («8 (8362) …», «+7 836 …»), выглядел одинаково.
 */
final class PhoneNormalizer
{
    public const MIN_DIGITS = 10;

    public const MAX_DIGITS = 15;

    public function digits(string $input): string
    {
        return (string) preg_replace('/\D+/', '', $input);
    }

    public function isPlausible(string $input): bool
    {
        $length = strlen($this->digits($input));

        return $length >= self::MIN_DIGITS && $length <= self::MAX_DIGITS;
    }

    public function normalize(string $input): string
    {
        $digits = $this->digits($input);

        // Российский номер: 8XXXXXXXXXX или 7XXXXXXXXXX → +7XXXXXXXXXX.
        if (strlen($digits) === 11 && ($digits[0] === '8' || $digits[0] === '7')) {
            return '+7'.substr($digits, 1);
        }

        // Десять цифр без кода страны — считаем российским.
        if (strlen($digits) === 10) {
            return '+7'.$digits;
        }

        return '+'.$digits;
    }
}
