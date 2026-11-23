<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Родитель услуги — только направление верхнего уровня: дерево услуг
 * не глубже двух уровней, без ссылок на себя.
 */
final readonly class TwoLevelParent implements ValidationRule
{
    public function __construct(
        private ?Service $record,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_numeric($value)) {
            $fail('Выбрано недопустимое направление.');

            return;
        }

        $parent = Service::query()->find((int) $value);

        if ($parent === null) {
            $fail('Выбранное направление не найдено.');

            return;
        }

        if ($this->record !== null && $parent->id === $this->record->id) {
            $fail('Услуга не может входить сама в себя.');

            return;
        }

        if (! $parent->isDirection()) {
            $fail('Выберите направление верхнего уровня: вложенность — не больше двух уровней.');

            return;
        }

        if ($this->record !== null && $this->record->exists && $this->record->children()->exists()) {
            $fail('В этой услуге есть вложенные услуги, поэтому сама она может быть только направлением.');
        }
    }
}
