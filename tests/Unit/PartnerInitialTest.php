<?php

declare(strict_types=1);

use App\Models\Partner;

it('берёт букву из названия без формы собственности и кавычек', function (string $name, string $letter): void {
    expect((new Partner(['name' => $name]))->initial())->toBe($letter);
})->with([
    'ООО в кавычках' => ['ООО «Демо Строй»', 'Д'],
    'без формы' => ['СК М ГРУПП', 'С'],
    'ИП' => ['ИП Петров', 'П'],
    'строчная' => ['стройка', 'С'],
    'только форма' => ['ООО', 'О'],
]);
