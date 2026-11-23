<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Calculator;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Страница калькулятора «Предварительный расчёт». Без ставок в админке страницы нет. */
final readonly class CalculatorController
{
    public function __construct(
        private Calculator $calculator,
    ) {}

    public function __invoke(): View
    {
        $rates = $this->calculator->rates();

        if ($rates->isEmpty()) {
            throw new NotFoundHttpException;
        }

        return view('calculator.index', ['rates' => $rates]);
    }
}
