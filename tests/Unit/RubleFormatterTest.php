<?php

declare(strict_types=1);

use App\Services\RubleFormatter;

$nbsp = "\u{00A0}";

it('показывает копейки рублями с неразрывными пробелами', function (int $kopecks, string $expected) use ($nbsp): void {
    expect((new RubleFormatter)->format($kopecks))->toBe(str_replace(' ', $nbsp, $expected));
})->with([
    'целые рубли' => [150_000, '1 500 ₽'],
    'с копейками' => [150_050, '1 500,50 ₽'],
    'одна копейка' => [1, '0,01 ₽'],
    'миллион' => [100_000_000, '1 000 000 ₽'],
    'ноль' => [0, '0 ₽'],
]);

it('готовит значение для поля ввода без пробелов', function (int $kopecks, string $expected): void {
    expect((new RubleFormatter)->toInput($kopecks))->toBe($expected);
})->with([
    [150_000, '1500'],
    [150_050, '1500,50'],
    [150_005, '1500,05'],
]);

it('готовит сумму для микроразметки: точка, без пробелов', function (int $kopecks, string $expected): void {
    expect((new RubleFormatter)->toDecimal($kopecks))->toBe($expected);
})->with([
    [150_000, '1500'],
    [150_050, '1500.50'],
    [100_000_000, '1000000'],
]);

it('переводит рубли из поля ввода в копейки точно', function (string $input, ?int $expected): void {
    expect((new RubleFormatter)->parse($input))->toBe($expected);
})->with([
    'целое' => ['1500', 150_000],
    'запятая' => ['1500,50', 150_050],
    'точка' => ['1500.5', 150_050],
    'одна копейка' => ['0,01', 1],
    'с пробелами' => ['1 500,50', 150_050],
    'максимум' => ['999999999,99', 99_999_999_999],
    'три знака после запятой' => ['1500,505', null],
    'больше максимума' => ['1000000000', null],
    'буквы' => ['тысяча', null],
    'минус' => ['-100', null],
    'пусто' => ['', null],
]);

it('не теряет копейки на сумме, где float ошибся бы', function (): void {
    // 0.1 + 0.2 во float = 0.30000000000000004; у нас — ровно 30 копеек.
    $rubles = new RubleFormatter;
    expect($rubles->parse('0,1') + $rubles->parse('0,2'))->toBe(30);
});
