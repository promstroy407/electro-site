<?php

declare(strict_types=1);

use App\Services\RussianPlural;

it('склоняет «услуга» после числа', function (int $count, string $word): void {
    expect((new RussianPlural)->choose($count, 'услуга', 'услуги', 'услуг'))->toBe($word);
})->with([
    [1, 'услуга'], [2, 'услуги'], [3, 'услуги'], [4, 'услуги'], [5, 'услуг'],
    [11, 'услуг'], [12, 'услуг'], [14, 'услуг'], [21, 'услуга'], [22, 'услуги'],
    [25, 'услуг'], [101, 'услуга'], [111, 'услуг'], [0, 'услуг'],
]);
