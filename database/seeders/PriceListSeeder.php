<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PriceUnit;
use App\Models\PriceItem;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Прайс «от» по средним ценам рынка, ноябрь 2026. Источники — прайсы
 * электриков Йошкар-Олы (masterabyta.ru, profivdom.ru) и прайсы
 * слаботочных компаний других городов со скидкой на регион (avanteko.ru —
 * Краснодар, −15 %; evek.ru — Москва, −20 %; psk54.ru — Новосибирск).
 * Это оценка, а не цены конкурентов один к одному: владелец правит их
 * в админке. Заливается и на проде (через ContentSeeder), только в пустой прайс.
 * Высоковольтные работы, заземление, пусконаладка, домофоны — «по запросу».
 */
final class PriceListSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->prices() as $slug => $items) {
            $service = Service::query()->where('slug', $slug)->first();
            if ($service === null) {
                continue;
            }

            foreach ($items as $index => [$title, $unit, $kopecks]) {
                PriceItem::query()->firstOrCreate(
                    ['service_id' => $service->id, 'title' => $title],
                    [
                        'unit' => $unit,
                        'price_kopecks' => $kopecks,
                        'is_from' => true,
                        'sort_order' => ($index + 1) * 10,
                        'is_published' => true,
                    ],
                );
            }
        }
    }

    /**
     * Слаг услуги => [название, единица, копейки].
     *
     * @return array<string, list<array{0: string, 1: PriceUnit, 2: int}>>
     */
    private function prices(): array
    {
        return [
            'elektromontazh-v-kvartire-i-dome' => [
                ['Прокладка кабеля открыто', PriceUnit::Meter, 6_000],
                ['Штробление стен под кабель', PriceUnit::Meter, 18_000],
                ['Установка подрозетника', PriceUnit::Piece, 8_000],
                ['Установка розетки или выключателя', PriceUnit::Piece, 13_000],
                ['Установка розетки с заземлением', PriceUnit::Piece, 20_000],
            ],
            'elektromontazh-v-ofise-i-magazine' => [
                ['Прокладка кабеля в коробе или кабель-канале', PriceUnit::Meter, 17_000],
                ['Установка розетки или выключателя', PriceUnit::Piece, 13_000],
            ],
            'elektromontazh-na-proizvodstve' => [
                ['Прокладка кабеля на скобах по бетону', PriceUnit::Meter, 15_500],
                ['Протяжка кабеля в трубе', PriceUnit::Meter, 34_000],
            ],
            'sborka-i-montazh-elektroshchitov' => [
                ['Сборка квартирного щита', PriceUnit::Set, 260_000],
                ['Установка однополюсного автомата', PriceUnit::Piece, 20_000],
                ['Установка УЗО или дифавтомата', PriceUnit::Piece, 35_000],
            ],
            'osveshchenie' => [
                ['Установка потолочного светильника', PriceUnit::Piece, 15_000],
                ['Установка точечного светильника', PriceUnit::Piece, 18_000],
                ['Установка настенного светильника', PriceUnit::Piece, 35_000],
            ],
            'sks' => [
                ['Монтаж и подключение розетки RJ-45 с кроссировкой', PriceUnit::Point, 40_000],
                ['Прокладка витой пары в лоток или короб', PriceUnit::Meter, 8_000],
                ['Тестирование линии', PriceUnit::Point, 6_000],
                ['Монтаж настенного шкафа', PriceUnit::Piece, 280_000],
            ],
            'lokalnye-seti-i-wi-fi' => [
                ['Установка и настройка точки доступа Wi-Fi', PriceUnit::Piece, 400_000],
            ],
            'aps' => [
                ['Монтаж дымового извещателя', PriceUnit::Piece, 30_000],
                ['Монтаж ручного извещателя', PriceUnit::Piece, 30_000],
                ['Монтаж приёмно-контрольного прибора', PriceUnit::Piece, 100_000],
                ['Прокладка кабеля шлейфа', PriceUnit::Meter, 3_000],
            ],
            'soue' => [
                ['Монтаж оповещателя или табло «Выход»', PriceUnit::Piece, 30_000],
            ],
            'videonablyudenie-dlya-biznesa' => [
                ['Монтаж IP-камеры', PriceUnit::Piece, 300_000],
                ['Монтаж и настройка видеорегистратора', PriceUnit::Piece, 430_000],
            ],
            'videonablyudenie-dlya-doma' => [
                ['Монтаж камеры', PriceUnit::Piece, 300_000],
                ['Настройка просмотра с телефона', PriceUnit::Service, 100_000],
            ],
            'skud-dlya-ofisov' => [
                ['Монтаж считывателя', PriceUnit::Piece, 130_000],
                ['Монтаж контроллера', PriceUnit::Piece, 260_000],
                ['Монтаж электромагнитного замка', PriceUnit::Piece, 260_000],
                ['Монтаж кнопки выхода', PriceUnit::Piece, 90_000],
            ],
            'turnikety-i-shlagbaumy' => [
                ['Монтаж турникета-трипода', PriceUnit::Piece, 850_000],
                ['Монтаж шлагбаума (без заливки основания)', PriceUnit::Piece, 1_280_000],
            ],
            'elektronnye-zamki' => [
                ['Установка электромеханического замка', PriceUnit::Piece, 340_000],
            ],
            'okhrannaya-signalizatsiya' => [
                ['Монтаж датчика движения или открытия', PriceUnit::Piece, 90_000],
                ['Монтаж прибора охранной сигнализации', PriceUnit::Piece, 430_000],
                ['Монтаж сирены', PriceUnit::Piece, 130_000],
            ],
        ];
    }
}
