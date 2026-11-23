<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CalculatorRate;
use App\Support\Estimate;
use App\Support\EstimateItem;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Калькулятор «Предварительный расчёт» на сайте. Цифры — ориентир,
 * точная смета — после осмотра объекта.
 */
final readonly class Calculator
{
    public function __construct(
        private RubleFormatter $rubles,
    ) {}

    /**
     * Работы, которые посетитель может отметить.
     *
     * @return Collection<int, CalculatorRate>
     */
    public function rates(): Collection
    {
        return CalculatorRate::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Расчёт по площади и отмеченным работам. Берутся только
     * опубликованные ставки — по текущим ценам из админки.
     *
     * @param  list<int>  $rateIds
     */
    public function estimate(int $areaM2, array $rateIds): Estimate
    {
        $items = array_values($this->rates()
            ->whereIn('id', $rateIds)
            ->map(fn (CalculatorRate $rate): EstimateItem => new EstimateItem($rate->title, $rate->rate_kopecks))
            ->all());

        if ($items === []) {
            throw new InvalidArgumentException('Не выбрано ни одной работы.');
        }

        return new Estimate($areaM2, $items);
    }

    /**
     * Текст расчёта для письма о заявке и админки.
     *
     * @return list<string>
     */
    public function describe(Estimate $estimate): array
    {
        $lines = ['Площадь: '.number_format($estimate->areaM2, 0, '', "\u{00A0}").' м²'];

        foreach ($estimate->items as $item) {
            $lines[] = $item->title.' — '.$this->rubles->format($item->rateKopecks).' за м²';
        }

        $lines[] = 'Итого: от '.$this->rubles->format($estimate->totalKopecks());

        return $lines;
    }
}
