<?php

declare(strict_types=1);

use App\Services\PhoneNormalizer;

it('приводит телефон к единому виду', function (string $input, string $expected): void {
    expect((new PhoneNormalizer)->normalize($input))->toBe($expected);
})->with([
    'с восьмёркой и скобками' => ['8 (8362) 12-34-56', '+78362123456'],
    'с +7 и пробелами' => ['+7 917 000 11 22', '+79170001122'],
    'семёрка без плюса' => ['79170001122', '+79170001122'],
    'десять цифр' => ['9170001122', '+79170001122'],
    'иностранный' => ['+375 29 123-45-67', '+375291234567'],
]);

it('отличает правдоподобный номер от мусора', function (string $input, bool $expected): void {
    expect((new PhoneNormalizer)->isPlausible($input))->toBe($expected);
})->with([
    'нормальный' => ['+7 917 000-11-22', true],
    'девять цифр' => ['917 000 11 2', false],
    'шестнадцать цифр' => ['+1234567890123456', false],
    'пусто' => ['', false],
]);
