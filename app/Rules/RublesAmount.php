<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\RubleFormatter;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Сумма в рублях из поля ввода: больше нуля, не больше двух знаков копеек. */
final readonly class RublesAmount implements ValidationRule
{
    public function __construct(
        private RubleFormatter $rubles,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $kopecks = is_string($value) || is_int($value) ? $this->rubles->parse((string) $value) : null;

        if ($kopecks === null || $kopecks <= 0) {
            $fail('Введите цену в рублях больше нуля: например, 1500 или 1500,50.');
        }
    }
}
