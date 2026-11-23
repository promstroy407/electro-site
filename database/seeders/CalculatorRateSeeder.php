<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CalculatorRate;
use Illuminate\Database\Seeder;

/**
 * Ставки калькулятора за 1 м² — назвал владелец 10.11.2026:
 * электрика 1 500 ₽, видеонаблюдение и сигнализация — по 500 ₽.
 * Дальше правятся в админке («Калькулятор»).
 */
final class CalculatorRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['Электромонтаж под ключ', 150_000, true],
            ['Видеонаблюдение', 50_000, false],
            ['Охранная сигнализация', 50_000, false],
        ];

        foreach ($rates as $index => [$title, $kopecks, $default]) {
            CalculatorRate::query()->create([
                'title' => $title,
                'rate_kopecks' => $kopecks,
                'is_default' => $default,
                'sort_order' => ($index + 1) * 10,
            ]);
        }
    }
}
